@extends('layout.app')

@push('style')
    <!-- Prism.js CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-tomorrow.min.css" rel="stylesheet" />
    <style>
        .api-section {
            background: var(--body-bg);
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            padding: 20px;
        }

        .code-block {
            background: #2d2d2d;
            border-radius: 4px;
            position: relative;
            margin: 15px 0;
        }

        .code-block pre {
            padding: 15px;
            margin: 0;
            color: #fff;
            overflow-x: auto;
        }

        .copy-btn {
            position: absolute;
            top: 5px;
            right: 5px;
            z-index: 100;
        }

        .method-badge {
            font-size: 14px;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: 600;
        }

        .param-table th {
            /* background-color: #f8f9fa; */
        }

        .api-key-section {
            /* background-color: #f8f9fa; */
            border-left: 4px solid #0d6efd;
            padding: 15px;
            margin-bottom: 20px;
        }

        .param-table {
            font-size: 14px;
            /* color: #333; */
        }

        .param-table th {
            text-transform: uppercase;
            font-weight: bold;
            text-align: left;
            padding: 12px;
        }

        .param-table td {
            padding: 10px;
            vertical-align: middle;
        }



        .badge {
            padding: 4px 8px;
            font-size: 12px;
            border-radius: 4px;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid py-4">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="carrd">
                    <div class="card-header">
                        <!-- API Documentation Header -->
                        <div class="api-section">
                            <h1 class="mb-4">Lead Submission API Documentation</h1>
                            <p class="lead">Integrate lead capture functionality into your applications using our REST API.
                            </p>
                        </div>
                    </div>
                    <div class="card-body">

                        <div class="row">
                            <div class="col-md-12">
                                <!-- Authentication Section -->
                                <div class="api-section">
                                    <h2 class="h4 mb-3">Authentication</h2>
                                    <div class="api-key-section">
                                        <h5>API Key Header</h5>
                                        <div class="code-block">
                                            <button class="btn btn-sm btn-primary copy-btn">Copy</button>
                                            <span class="px-2 mx-2">X-API-Key: </span> <pre><code id="apiKey">{{ Auth::user()?->company?->api_token }}</code></pre>
                                        </div>
                                        <p class="text-muted mt-2">Include this header in all API requests</p>
                                    </div>
                                </div>
                                <!-- Request Parameters -->
                                <div class="api-section">
                                    <h2 class="h4 mb-3">Request Parameters</h2>
                                    <p class="text-muted mb-3">
                                        Below is the list of all parameters required for the API request. Fields marked as
                                        <span class="badge bg-danger">Required</span> must be provided.
                                    </p>
                                    <div class="table-responsive">
                                        <table class="table param-table border">
                                            <thead class="bg-primary text-white">
                                                <tr>
                                                    <th>Parameter</th>
                                                    <th>Type</th>
                                                    <th>Changeable</th>
                                                    <th>Required</th>
                                                    <th>Description</th>
                                                </tr>
                                            </thead>
                                            <tbody>

                                                <tr>
                                                    <td><strong>uuid</strong></td>
                                                    <td><span class="badge bg-info text-white">String</span></td>
                                                    <td><span class="badge bg-secondary">No</span></td>
                                                    <td><span class="badge bg-danger">Yes</span></td>
                                                    <td>The universally unique identifier (UUID) of the web form.</td>
                                                </tr>

                                                <tr>
                                                    <td><strong>group_id</strong></td>
                                                    <td><span class="badge bg-info text-white">String</span></td>
                                                    <td><span class="badge bg-secondary">No</span></td>
                                                    <td><span class="badge bg-danger">Yes</span></td>
                                                    <td>A valid category group ID.</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>source_id</strong></td>
                                                    <td><span class="badge bg-info text-white">String</span></td>
                                                    <td><span class="badge bg-secondary">No</span></td>
                                                    <td><span class="badge bg-danger">Yes</span></td>
                                                    <td>A valid source category ID.</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>status_id</strong></td>
                                                    <td><span class="badge bg-info text-white">String</span></td>
                                                    <td><span class="badge bg-secondary">No</span></td>
                                                    <td><span class="badge bg-danger">Yes</span></td>
                                                    <td>A valid status category ID.</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>company_name</strong></td>
                                                    <td><span class="badge bg-info text-white">String</span></td>
                                                    <td><span class="badge bg-success">Yes</span></td>
                                                    <td><span class="badge bg-secondary">No</span></td>
                                                    <td>Client company name (max 255 chars).</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>title</strong></td>
                                                    <td><span class="badge bg-info text-white">String</span></td>
                                                    <td><span class="badge bg-success">Yes</span></td>
                                                    <td><span class="badge bg-danger">Yes</span></td>
                                                    <td>Contact title (max 255 chars).</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>first_name</strong></td>
                                                    <td><span class="badge bg-info text-white">String</span></td>
                                                    <td><span class="badge bg-success">Yes</span></td>
                                                    <td><span class="badge bg-danger">Yes</span></td>
                                                    <td>Contact's first name (max 255 chars).</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>last_name</strong></td>
                                                    <td><span class="badge bg-info text-white">String</span></td>
                                                    <td><span class="badge bg-success">Yes</span></td>
                                                    <td><span class="badge bg-danger">Yes</span></td>
                                                    <td>Contact's last name (max 255 chars).</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>email</strong></td>
                                                    <td><span class="badge bg-info text-white">String</span></td>
                                                    <td><span class="badge bg-success">Yes</span></td>
                                                    <td><span class="badge bg-danger">Yes</span></td>
                                                    <td>A valid email address for the contact.</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>phone</strong></td>
                                                    <td><span class="badge bg-info text-white">String</span></td>
                                                    <td><span class="badge bg-success">Yes</span></td>
                                                    <td><span class="badge bg-secondary">No</span></td>
                                                    <td>Phone number (7-15 chars).</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>preferred_contact_method</strong></td>
                                                    <td><span class="badge bg-info text-white">String</span></td>
                                                    <td><span class="badge bg-success">Yes</span></td>
                                                    <td><span class="badge bg-secondary">No</span></td>
                                                    <td>One of: <em>Email, Phone, In-Person</em>.</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>address_street_address</strong></td>
                                                    <td><span class="badge bg-info text-white">String</span></td>
                                                    <td><span class="badge bg-success">Yes</span></td>
                                                    <td><span class="badge bg-secondary">No</span></td>
                                                    <td>Street address (max 255 chars).</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>address_country_id</strong></td>
                                                    <td><span class="badge bg-info text-white">String</span></td>
                                                    <td><span class="badge bg-success">Yes</span></td>
                                                    <td><span class="badge bg-secondary">No</span></td>
                                                    <td>Valid country ID. <br /><a href="{{ route('download.countries') }}"
                                                            class="btn btn-outline-primary btn-xl">
                                                            <i class="fas fa-download me-2"></i>
                                                            {{ __('crud.Download Countries List for Country ID') }}
                                                        </a></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>address_city_name</strong></td>
                                                    <td><span class="badge bg-info text-white">String</span></td>
                                                    <td><span class="badge bg-success">Yes</span></td>
                                                    <td><span class="badge bg-secondary">No</span></td>
                                                    <td>City name (max 255 chars).</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>address_pincode</strong></td>
                                                    <td><span class="badge bg-info text-white">String</span></td>
                                                    <td><span class="badge bg-success">Yes</span></td>
                                                    <td><span class="badge bg-secondary">No</span></td>
                                                    <td>Postal/ZIP code (max 20 chars).</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>


                                <!-- Endpoint Section -->
                                <div class="api-section">
                                    <h2 class="h4 mb-3">Endpoint</h2>
                                    <div class="d-flex align-items-center gap-2 mb-3">
                                        <span class="method-badge bg-success text-white">POST</span>
                                        <code
                                            class="bg-light p-2 rounded">{{ config('app.url') . '/api/leads/create' }}</code>
                                    </div>
                                </div>
                                <!-- Example Request -->
                                <div class="api-section">
                                    <h2 class="h4 mb-3">Example Request</h2>
                                    <div class="code-block">
                                        <button class="btn btn-sm btn-primary copy-btn">Copy</button>
                                        <pre><code class="language-json">{
    // fixed values as per this form, please don't change these
   
    "uuid": "{{ $form->uuid }}",
    "group_id": "{{ $form->group_id }}",
    "source_id": "{{ $form->source_id }}",
    "status_id": "{{ $form->status_id }}",
    // dynamic values below
    "company_name": "Acme Corp",
    "title": "Mr.",
    "first_name": "John",
    "last_name": "Doe",
    "email": "john.doe@example.com",
    "phone": "1234567890",
    "preferred_contact_method": "Email",
    "address_street_address": "123 Main St",
    "address_country_id": "1", // it should be from countries lists id
    "address_city_name": "New York",
    "address_pincode": "10001"
}</code></pre>
                                    </div>
                                </div>

                                <!-- Example Response -->
                                <div class="api-section">
                                    <h2 class="h4 mb-3">Example Response</h2>
                                    <div class="code-block">
                                        <button class="btn btn-sm btn-primary copy-btn">Copy</button>
                                        <pre><code class="language-json">{
    "success": true,
    "message": "Lead created successfully.",
    "data": {
        "lead_id": 2,
        "created_at": "2025-01-20T06:44:49+00:00"
        }
                                        
}</code></pre>
                                    </div>
                                </div>
                            </div>
                        </div>





                        <!-- Request Parameters -->







                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/prism.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-json.min.js"></script>
    <script>
        $(document).ready(function() {
            // Copy button functionality
            $('.copy-btn').click(function() {
                const codeBlock = $(this).siblings('pre').text();
                navigator.clipboard.writeText(codeBlock).then(() => {
                    const originalText = $(this).text();
                    $(this).text('Copied!');
                    setTimeout(() => {
                        $(this).text(originalText);
                    }, 2000);
                });
            });


        });
    </script>
@endpush
