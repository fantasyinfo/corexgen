<?php

namespace Modules\PaypalGatewayModule\App\Services;

use App\Contracts\Payments\PaymentGatewayInterface;
use App\Http\Controllers\PaymentGatewayController;
use App\Models\PaymentGatewayStoreSession;
use Illuminate\Http\Request;
use App\Http\Controllers\CompanyRegisterController;
use App\Models\PaymentGateway;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Str;

class PayPalPaymentGateway implements PaymentGatewayInterface
{
    protected $client;
    protected $clientId;
    protected $clientSecret;
    protected $mode;
    protected $baseUrl;

    public function __construct()
    {
        $this->client = new Client();
    }

    protected function initializeCredentials($key = null, $value = null, $mode = null)
    {
        $paypal = PaymentGateway::where('name', 'Paypal')->first();

        if (!$paypal) {
            throw new \Exception('PayPal configuration not found');
        }

        if ($key != null && $value != null && $mode != null) {
            $this->clientId = $key;
            $this->clientSecret = $value;
            $this->mode = $paypal->mode === $mode;
        } else {
            $this->clientId = $paypal->config_key;
            $this->clientSecret = $paypal->config_value;
            $this->mode = $paypal->mode === 'LIVE' ? 'live' : 'sandbox';
        }


        $this->baseUrl = $this->mode === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';
    }

    protected function getAccessToken($key = null, $value = null, $mode = null)
    {
        if ($key != null && $value != null && $mode != null) {
            $this->initializeCredentials($key, $value, $mode);
        } else {
            $this->initializeCredentials();
        }


        try {
            $response = $this->client->post("{$this->baseUrl}/v1/oauth2/token", [
                'auth' => [$this->clientId, $this->clientSecret],
                'form_params' => [
                    'grant_type' => 'client_credentials'
                ]
            ]);
            \Log::info('Entire Response', [$response]);

            $data = json_decode($response->getBody(), true);
            \Log::info('Access Token Init', [$data]);

            return $data['access_token'];
        } catch (RequestException $e) {
            \Log::error('PayPal Access Token Error', [
                'message' => $e->getMessage(),
                'response' => $e->getResponse() ? $e->getResponse()->getBody()->getContents() : null
            ]);
            throw new \Exception('Failed to obtain PayPal access token');
        }
    }

    public function initialize(array $paymentDetails)
    {
        try {
            $uuid = Str::uuid();
            if (isset($paymentDetails['config_key']) && isset($paymentDetails['config_value']) && isset($paymentDetails['mode'])) {
                $accessToken = $this->getAccessToken(
                    $paymentDetails['config_key'],
                    $paymentDetails['config_value'],
                    $paymentDetails['mode']
                );

                PaymentGatewayStoreSession::updateOrCreate(
                    ['session_id' => $uuid, 'company_id' => $paymentDetails['metadata']['company_id']],
                    [
                        'config_key' => $paymentDetails['config_key'],
                        'config_value' => encrypt($paymentDetails['config_value']),
                        'mode' => $paymentDetails['mode'],

                    ]
                );
            } else {
                $accessToken = $this->getAccessToken();
            }


            $payload = [
                'intent' => 'CAPTURE',
                'purchase_units' => [
                    [
                        'amount' => [
                            'currency_code' => $paymentDetails['currency'] ?? 'USD',
                            'value' => number_format($paymentDetails['amount'], 2, '.', '')
                        ],
                        'description' => $paymentDetails['description'] ?? 'Subscription',
                        'custom_id' => json_encode([
                            'company_id' => $paymentDetails['metadata']['company_id'] ?? null,
                            'plan_id' => $paymentDetails['metadata']['plan_id'] ?? null,
                            'is_plan_upgrade' => $paymentDetails['metadata']['is_plan_upgrade'] ?? null,
                            'invoice_uuid' => $paymentDetails['metadata']['invoice_uuid'] ?? null,
                            'is_invoice_paying' => $paymentDetails['metadata']['is_invoice_paying'] ?? null,
                            'is_company_registration' => $paymentDetails['metadata']['is_company_registration'] ?? null,

                        ])
                    ]
                ],
                'application_context' => [
                    'return_url' => route('payment.success', ['gateway' => 'paypal']) . '?_token=' . $uuid,
                    'cancel_url' => route('payment.cancel', ['gateway' => 'paypal']),
                    'user_action' => 'PAY_NOW'
                ]
            ];

            $response = $this->client->post("{$this->baseUrl}/v2/checkout/orders", [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => "Bearer {$accessToken}"
                ],
                'json' => $payload
            ]);

            $data = json_decode($response->getBody(), true);

            // Find the approval URL
            $approvalUrl = collect($data['links'])
                ->firstWhere('rel', 'approve')['href'];

            return $approvalUrl;
        } catch (RequestException $e) {
            \Log::error('PayPal Order Creation Error', [
                'message' => $e->getMessage(),
                'response' => $e->getResponse() ? $e->getResponse()->getBody()->getContents() : null
            ]);
            throw new \Exception('Failed to create PayPal order');
        }
    }

    public function processPayment($paymentData)
    {
        // \Log::info('PayPal Payment Data Received outside', $paymentData);
        try {
            // Log all incoming payment data for debugging
            // \Log::info('PayPal Payment Data Received', $paymentData);

            // \Log::info('Access Token Process', [$paymentData['token']]);
            // Extract order ID from the incoming data
            $orderId = $paymentData['token'] ?? null;

            if (!$orderId) {
                \Log::error('Payment failed. Missing order ID.', $paymentData);
                return redirect()->route('payment.failed')->with('error', 'Invalid payment session');
            }


            $configGateway = false;
            if (isset($paymentData['_token'])) {
                $configGateway = PaymentGatewayStoreSession::where('session_id', $paymentData['_token'])->first();
            }

            // If not in metadata, try to get from database backup

            $configKey = $configValue = $mode = null;
            if ($configGateway) {
                $configKey = $configGateway->config_key;
                $configValue = decrypt($configGateway->config_value);
                $mode = $configGateway->mode;
                \Log::info('Retrieved Stripe config from database backup');
            }


            // Set API key based on available config
            if ($configKey && $configValue && $mode) {
                $accessToken = $this->getAccessToken($configKey, $configValue, $mode);
                \Log::info('Using retrieved configuration for payment processing');
            } else {
                $accessToken = $this->getAccessToken();
                \Log::info('Using default configuration for payment processing');
            }


            // Capture the order using the token/order ID
            $response = $this->client->post("{$this->baseUrl}/v2/checkout/orders/{$orderId}/capture", [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => "Bearer {$accessToken}"
                ]
            ]);

            $captureData = json_decode($response->getBody(), true);

            \Log::info('Capture Data', $captureData);
            if ($captureData['status'] !== 'COMPLETED') {
                \Log::error('Payment failed. Payment status is not completed.', $captureData);
                return redirect()->route('payment.failed')->with('error', 'Payment not completed');
            }


            $customIdRaw = $captureData['purchase_units'][0]['payments']['captures'][0]['custom_id'] ?? '{}';
            $customMetadata = json_decode($customIdRaw, true);

            // Get payment capture details
            $paymentCapture = $captureData['purchase_units'][0]['payments']['captures'][0];

            $paymentDetails = [
                'payment_gateway' => 'Paypal',
                'payment_type' => 'ONLINE',
                'transaction_reference' => json_encode($paymentCapture),
                'transaction_id' => $paymentCapture['id'],
                'amount' => $paymentCapture['amount']['value'] ?? '',
                'currency' => $paymentCapture['amount']['currency_code'] ?? '',
                'company_id' => $customMetadata['company_id'] ?? '',
                'plan_id' => $customMetadata['plan_id'] ?? '',
                'invoice_uuid' => $customMetadata['invoice_uuid'] ?? '',
                'response' => $captureData,
                'is_plan_upgrade' => filter_var($customMetadata['is_plan_upgrade'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'is_invoice_paying' => filter_var($customMetadata['is_invoice_paying'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'is_company_registration' => filter_var($customMetadata['is_company_registration'] ?? false, FILTER_VALIDATE_BOOLEAN),
            ];


            if ($configGateway) {
                // Clean up stored config if it exists
                PaymentGatewayStoreSession::where('session_id', $paymentData['_token'])->delete();
            }

            return app(PaymentGatewayController::class)->handlePaymentGatewaysSuccessResponse($paymentDetails);


        } catch (\Exception $e) {
            // Log the full exception for detailed debugging
            \Log::error('PayPal Payment Processing Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Provide a more informative error message
            return redirect()->route('payment.failed')->with('error', 'Payment processing failed: ' . $e->getMessage());
        }
    }

    public function refund(string $transactionId, float $amount)
    {
        try {
            $accessToken = $this->getAccessToken();

            $response = $this->client->post("{$this->baseUrl}/v2/payments/captures/{$transactionId}/refund", [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => "Bearer {$accessToken}"
                ],
                'json' => [
                    'amount' => [
                        'currency_code' => 'USD', // You might want to make this dynamic
                        'value' => number_format($amount, 2, '.', '')
                    ]
                ]
            ]);

            $refundData = json_decode($response->getBody(), true);

            return [
                'status' => $refundData['status'],
                'refund_id' => $refundData['id']
            ];
        } catch (RequestException $e) {
            \Log::error('PayPal Refund Error', [
                'message' => $e->getMessage(),
                'response' => $e->getResponse() ? $e->getResponse()->getBody()->getContents() : null
            ]);
            throw new \Exception('Failed to process refund');
        }
    }

    public function handleWebhook(Request $request)
    {
        // Implement webhook verification and handling
        // Verify webhook authenticity using access token and PayPal's webhook signature verification
    }
}
