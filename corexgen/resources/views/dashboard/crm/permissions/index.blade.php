@extends('layout.app')

@section('content')
    <div class="container-fluid">
        <div class=" p-3">
          
            @include('layout.components.header-buttons')
            <div class="shadow-sm rounded">

                @if (hasPermission('PERMISSIONS.READ_ALL') || hasPermission('PERMISSIONS.READ'))
                    <div class="table-responsive  table-bg">

                        <table id="permissionTable" class="table table-striped table-bordered ui celled">
                            <thead>
                                <tr>
                                    <th> {{ __('crm_permissions.Role Name') }}</th>
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

  

            const dbTableAjax = $("#permissionTable").DataTable({
                processing: true,
                serverSide: true,
                stateSave: true,
                 language: {
                    "lengthMenu": "_MENU_ per page",
                },
                ajax: {
                    url: "{{ route(getPanelRoutes($module . '.index')) }}",
                },
                columns: [{
                        data: 'role_name',
                        name: 'role_name'
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false
                    },
                ],
            });
        });
    </script>
@endpush
