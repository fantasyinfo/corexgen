@extends('layout.app')

@section('content')
    <div class="container-fluid ">
        <div class="">
            {{-- @include('layout.components.header-buttons') --}}

            <div class="shadow-sm rounded ">
                {{-- @include('dashboard.role.components.role-filters') --}}


                @if (hasPermission('PAYMENTGATEWAYS.READ_ALL') || hasPermission('PAYMENTGATEWAYS.READ'))
                    <div class="table-responsive table-bg ">

                        <table id="roleTable" class="table table-striped table-bordered ui celled">
                            <thead>
                                <tr>
                                    
                                    <th> {{ __('paymentgateway.Logo') }}</th>
                                    <th>{{ __('paymentgateway.Website') }}</th>
                                    <th>{{ __('paymentgateway.Name') }}</th>
                                    <th>{{ __('paymentgateway.Type') }}</th>
                                    <th>{{ __('paymentgateway.Mode') }}</th>
                                    <th>{{ __('crud.Status') }}</th>
                                    {{-- <th>{{ __('crud.Created At') }}</th> --}}

                                    <th class="text-end">{{ __('crud.Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>


                    </div>
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
    <script type="text/javascript">
        $(document).ready(function() {

            const nameFilter = $('#nameFilter');
            const statusFilter = $('#statusFilter');
            const startDateFilter = $('#startDateFilter');
            const endDateFilter = $('#endDateFilter');

            const dbTableAjax = $("#roleTable").DataTable({
                processing: true,
                serverSide: true,
                stateSave: true,
                language: {
                    "lengthMenu": "_MENU_ per page",
                },
                ajax: {
                    url: "{{ route(getPanelRoutes($module . '.index')) }}",
                    data: function(d) {
                        // Add filters if required
                        d.name = nameFilter.val();
                        d.status = $('#statusFilter').val();
                        d.start_date = $('#startDateFilter').val();
                        d.end_date = $('#endDateFilter').val();
                    },
                },
                columns: [
                    {
                        data: 'logo',
                        name: 'logo'
                    },
                    {
                        data: 'official_website',
                        name: 'official_website'
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'type',
                        name: 'type'
                    },
                    {
                        data: 'mode',
                        name: 'mode'
                    },
                    {
                        data: 'status',
                        name: 'status'
                    },
                    // {
                    //     data: 'created_at',
                    //     name: 'created_at'
                    // },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false
                    },
                ],
            });

            // Clear Filter Button
            $('#clearFilter').on('click', function() {
                // Reset all filter input fields
                nameFilter.val('');
                statusFilter.val('');
                startDateFilter.val('');
                endDateFilter.val('');
                // Reset the DataTable's search and reload
                dbTableAjax.ajax.reload();
            });

            $("#filterBtn").click(function() {
                dbTableAjax.ajax.reload();
                const filterSidebar = document.getElementById("filterSidebar");
                filterSidebar.classList.remove('show');
            });


        });
    </script>
@endpush
