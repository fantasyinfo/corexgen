@extends('layout.app')

@section('content')
    <div class="container-fluid">
        <div class="">

            @include('layout.components.header-buttons')


            <div class="shadow-sm rounded">

                @include('dashboard.users.components.users-filters')
                
                {{-- @include('layout.components.header-stats') --}}
                @include('layout.components.header-stats-new')
                @include('layout.components.bulk-import-modal')


                @if (hasPermission('USERS.READ_ALL') || hasPermission('USERS.READ'))

                @php

                $columns = [
                    [
                        'data' => null,
                        'label' => new \Illuminate\Support\HtmlString('<input type="checkbox" id="select-all" />'),
                        'orderable' => false,
                        'searchable' => false,
                        'width' => '10px',
                        'render' => 'function(data, type, row) {
                            return `<input type="checkbox" class="bulk-select" data-id="${row.id}" />`;
                        }',
                    ],
                    [
                        'data' => 'name',
                        'name' => 'name',
                        'label' => __('users.Name'),
                        'searchable' => true,
                        'orderable' => true,
                        'width' => '150px',
                    ],
                    [
                        'data' => 'email',
                        'name' => 'email',
                        'label' => __('users.Email'),
                        'searchable' => true,
                        'orderable' => true,
                        'width' => '100px',
                    ],
                    [
                        'data' => 'role.role_name',
                        'name' => 'role.role_name',
                        'label' => __('users.Role'),
                        'searchable' => false,
                        'orderable' => false,
                        'width' => '150px',
                    ],
                    
                    [
                        'data' => 'status',
                        'name' => 'status',
                        'label' => __('crud.Status'),
                        'searchable' => false,
                        'orderable' => true,
                        'width' => '100px',
                    ],
                    [
                        'data' => 'created_at',
                        'name' => 'created_at',
                        'label' => __('crud.Created At'),
                        'searchable' => true,
                        'orderable' => true,
                        'width' => '100px',
                    ],
                    [
                        'data' => 'actions',
                        'name' => 'actions',
                        'label' => __('crud.Actions'),
                        'orderable' => false,
                        'searchable' => false,
                        'width' => '100px',
                    ],
                ];
            @endphp

            <x-data-table id="usersTable" :columns="$columns" :ajax-url="route(getPanelRoutes($module . '.index'))" :is-checkbox="true"
                :bulk-delete-url="route(getPanelRoutes($module . '.bulkDelete'))" :csrf-token="csrf_token()" />

            
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
     
        // change password
        $(document).ready(function() {

            const $savePasswordButton = $("#savePasswordButton");
            const $errorMessage = $("#errorMessage");
            const $newPasswordInput = $("#newPassword");
            const $confirmPasswordInput = $("#confirmPassword");


            let dataId = "";



            // Event listener for change password link
            $(document).on('click', '.change-password-link', function(event) {
                event.preventDefault();

                // Get ID from closest row or data attribute
                let dataId = $(this).data('id');

                console.log('Clicked Change Password - ID:', dataId);

                // Set ID in modal or global variable
                $('#changePasswordModal').data('user-id', dataId);
            });

            // Event listener for save password button
            $savePasswordButton.on('click', function() {
                let dataId = $('#changePasswordModal').data('user-id');
                // Reset error message
                $errorMessage.addClass('d-none').text('');

                const newPassword = $newPasswordInput.val().trim();
                const confirmPassword = $confirmPasswordInput.val().trim();

                // Validation
                if (!newPassword || !confirmPassword) {
                    $errorMessage.text("Both fields are required.").removeClass('d-none');
                    return;
                }

                if (newPassword !== confirmPassword) {
                    $errorMessage.text("Passwords do not match.").removeClass('d-none');
                    return;
                }

                // AJAX request to change password
                $.ajax({
                    url: "{{ route(getPanelRoutes($module . '.changePassword')) }}",
                    method: "POST",
                    data: {
                        id: dataId,
                        password: newPassword,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(data) {
                        if (data.success) {
                            alert("Password updated successfully!");
                            $('#changePasswordModal').modal('hide');
                        } else {
                            $errorMessage.text(data.message || "An error occurred.")
                                .removeClass('d-none');
                        }
                    },
                    error: function(xhr, status, error) {
                        $errorMessage.text("An error occurred. Please try again.").removeClass(
                            'd-none');
                        console.error("Error:", error);
                    }
                });
            });
        });
    </script>
@endpush
