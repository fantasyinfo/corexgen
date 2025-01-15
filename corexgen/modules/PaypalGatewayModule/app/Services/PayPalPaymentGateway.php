<?php

namespace Modules\PaypalGatewayModule\App\Services;

use App\Contracts\Payments\PaymentGatewayInterface;
use Illuminate\Http\Request;
use App\Http\Controllers\CompanyRegisterController;
use App\Models\PaymentGateway;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

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

            $data = json_decode($response->getBody(), true);
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
            if (isset($paymentDetails['config_key']) && isset($paymentDetails['config_value']) && isset($paymentDetails['mode'])) {
                $accessToken = $this->getAccessToken(
                    $paymentDetails['config_key'],
                    $paymentDetails['config_value'],
                    $paymentDetails['mode']
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
                        ])
                    ]
                ],
                'application_context' => [
                    'return_url' => route('payment.success', ['gateway' => 'paypal']),
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
        \Log::info('PayPal Payment Data Received outside', $paymentData);
        try {
            // Log all incoming payment data for debugging
            \Log::info('PayPal Payment Data Received', $paymentData);

            // Extract order ID from the incoming data
            $orderId = $paymentData['token'] ?? null;

            if (!$orderId) {
                \Log::error('Payment failed. Missing order ID.', $paymentData);
                return redirect()->route('payment.failed')->with('error', 'Invalid payment session');
            }

            $accessToken = $this->getAccessToken();

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
                'payment_gateway' => 'paypal',
                'payment_type' => 'ONLINE',
                'transaction_reference' => json_encode($paymentCapture),
                'transaction_id' => $paymentCapture['id'],
                'amount' => $paymentCapture['amount']['value'],
                'currency' => $paymentCapture['amount']['currency_code'],
                'net_amount' => $paymentCapture['seller_receivable_breakdown']['net_amount']['value'] ?? $paymentCapture['amount']['value'],
                'paypal_fee' => $paymentCapture['seller_receivable_breakdown']['paypal_fee']['value'] ?? 0,
                'company_id' => $customMetadata['company_id'] ?? null,
                'plan_id' => $customMetadata['plan_id'] ?? null,
            ];

            \Log::info('Payment processed successfully', $paymentDetails);

            if (isset($customMetadata['is_plan_upgrade']) && $customMetadata['is_plan_upgrade']) {
                return app(CompanyRegisterController::class)->upgradePlanForCompany($paymentDetails);
            }

            return app(CompanyRegisterController::class)->storeCompnayAfterPaymentOnboading($paymentDetails);
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