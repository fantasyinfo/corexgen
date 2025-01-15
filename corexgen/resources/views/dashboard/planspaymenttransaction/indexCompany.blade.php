@extends('layout.app')

@section('content')
    <div class="container-fluid">

        <!-- Bootstrap Modal for Confirmation -->
        <div class="modal fade" id="transactionModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteModalLabel">Transaction Refrence</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <table id="transactionDetailsTable" class="table table-striped table-bordered ui celled">
                            <!-- Populate rows via JavaScript -->
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="">
            @include('layout.components.header-buttons')

            <div class="shadow-sm rounded">

                {{-- @include('dashboard.crm.users.components.users-filters') --}}
                {{-- @include('layout.components.bulk-import-modal') --}}


                @if (hasPermission('PAYMENTSTRANSACTIONS.READ_ALL') || hasPermission('PAYMENTSTRANSACTIONS.READ'))
                    @php

                        $columns = [
                            [
                                'data' => 'currency',
                                'name' => 'currency',
                                'label' => __('planspaymenttransaction.Currency'),
                                'searchable' => true,
                                'orderable' => true,
                                'width' => '100px',
                            ],
                            [
                                'data' => 'amount',
                                'name' => 'amount',
                                'label' => __('planspaymenttransaction.Amount'),
                                'searchable' => true,
                                'orderable' => true,
                                'width' => '100px',
                            ],
                            [
                                'data' => 'payment_gateway',
                                'name' => 'payment_gateway',
                                'label' => __('planspaymenttransaction.Gateway'),
                                'searchable' => true,
                                'orderable' => true,
                                'width' => '100px',
                            ],
                            [
                                'data' => 'payment_type',
                                'name' => 'payment_type',
                                'label' => __('planspaymenttransaction.Type'),
                                'searchable' => true,
                                'orderable' => true,
                                'width' => '100px',
                            ],
                            [
                                'data' => 'transaction_date',
                                'name' => 'transaction_date',
                                'label' => __('planspaymenttransaction.Transaction Date'),
                                'searchable' => true,
                                'orderable' => true,
                                'width' => '150px',
                            ],
                            [
                                'data' => 'transaction_reference',
                                'name' => 'transaction_reference',
                                'label' => __('planspaymenttransaction.Transaction Refrence'),
                                'searchable' => true,
                                'orderable' => true,
                                'width' => '150px',
                            ],

                            // [
                            //     'data'=> 'status',
                            //     'name'=> 'status',
                            //     'label'=> __('crud.Status'),
                            //     'searchable'=> false,
                            //     'orderable'=> true,
                            //     'width'=> '100px',
                            // ],
                            [
                                'data' => 'created_at',
                                'name' => 'created_at',
                                'label' => __('crud.Created At'),
                                'searchable' => true,
                                'orderable' => true,
                                'width' => '100px',
                            ],
                            // [
                            //     'data'=> 'actions',
                            //     'name'=> 'actions',
                            //     'label'=> 'label' => __('crud.Action'),
                            //     'orderable'=> false,
                            //     'searchable'=> false,
                            //     'width'=> '100px',
                            // ],
                        ];
                    @endphp
                    <x-data-table id="plansPaymentCompanyTransactionTable" :columns="$columns" :ajax-url="route(getPanelRoutes($module . '.index'))" />
                @else
                    {{-- no permissions to view --}}
                    <div class="no-data-found">
                        <i class="fas fa-ban"></i>
                        <span class="mx-2">{{ __('crud.You do not have permission to view the table') }}</span>
                    </div>
                @endif





            </div>
        </div>
    </div>
@endsection


@push('scripts')
    <script>
        // Using querySelector instead of jQuery
        document.addEventListener('DOMContentLoaded', () => {
            const transactionModal = document.querySelector('#transactionModal');
            if (!transactionModal) {
                console.error('Transaction modal not found in the DOM');
                return;
            }

            // Function to flatten nested objects
            const flattenObject = (obj, prefix = '') => {
                let flattened = {};

                for (const [key, value] of Object.entries(obj)) {
                    // Handle arrays
                    if (Array.isArray(value)) {
                        value.forEach((item, index) => {
                            if (typeof item === 'object' && item !== null) {
                                const nestedObj = flattenObject(item, `${key}[${index}]`);
                                Object.assign(flattened, nestedObj);
                            } else {
                                flattened[`${key}[${index}]`] = item;
                            }
                        });
                    }
                    // Handle nested objects
                    else if (typeof value === 'object' && value !== null) {
                        const nestedObj = flattenObject(value, prefix ? `${prefix}.${key}` : key);
                        Object.assign(flattened, nestedObj);
                    }
                    // Handle primitive values
                    else {
                        const finalKey = prefix ? `${prefix}.${key}` : key;
                        flattened[finalKey] = value;
                    }
                }

                return flattened;
            };

            // Function to format value based on type
            const formatValue = (value) => {
                if (value === null) return 'null';
                if (value === undefined) return 'undefined';
                if (value === true) return 'true';
                if (value === false) return 'false';
                return value;
            };

            transactionModal.addEventListener('show.bs.modal', (event) => {
                console.log('Modal show triggered');
                const button = event.relatedTarget;
                const reference = button.dataset.refrence;

                const table = document.querySelector('#transactionDetailsTable');
                if (!table) {
                    console.error('Table element not found');
                    return;
                }

                let tableBody = table.querySelector('tbody');
                if (!tableBody) {
                    tableBody = document.createElement('tbody');
                    table.appendChild(tableBody);
                }

                try {
                    const referenceData = typeof reference === 'string' ?
                        JSON.parse(reference) :
                        reference;

                    tableBody.innerHTML = ''; // Clear previous rows

                    // Add table header if not exists
                    let thead = table.querySelector('thead');
                    if (!thead) {
                        thead = document.createElement('thead');
                        thead.innerHTML = `
                    <tr>
                        <th>Field</th>
                        <th>Value</th>
                    </tr>
                `;
                        table.insertBefore(thead, tableBody);
                    }

                    if (referenceData && typeof referenceData === 'object') {
                        const flattenedData = flattenObject(referenceData);

                        Object.entries(flattenedData)
                            .sort(([keyA], [keyB]) => keyA.localeCompare(keyB))
                            .forEach(([key, value]) => {
                                const row = document.createElement('tr');
                                row.innerHTML = `
                            <td class="fw-bold">${key}</td>
                            <td>${formatValue(value)}</td>
                        `;
                                tableBody.appendChild(row);
                            });
                    } else {
                        console.error('Reference data is not an object:', referenceData);
                    }
                } catch (error) {
                    console.error('Error processing reference data:', error);
                    console.log('Raw reference data:', reference);
                }
            });
        });
    </script>
@endpush
