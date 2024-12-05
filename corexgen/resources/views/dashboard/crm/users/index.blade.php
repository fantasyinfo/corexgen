@extends('layout.app')

@section('content')
    <div class="container-fluid">
        <div class="p-3">
            @include('layout.components.header-buttons')

            <div class="shadow-sm rounded">

                @include('dashboard.crm.users.components.users-filters')
                @include('layout.components.bulk-import-modal')


                @if (hasPermission('USERS.READ_ALL') || hasPermission('USERS.READ'))
                    <div class="table-responsive table-bg">

                        <table id="userTable" class="table table-striped table-bordered ui celled">
                            <thead>
                                <tr>
                                    <th>
                                        <input type="checkbox" id="select-all" />
                                    </th>
                                    <th> {{ __('users.Name') }}</th>
                                    <th>{{ __('users.Email') }}</th>
                                    <th>{{ __('users.Role') }}</th>
                                    <th>{{ __('crud.Status') }}</th>
                                    <th>{{ __('crud.Created At') }}</th>

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

@include('layout.components.bulk-import-js')

@push('scripts')
    <script type="text/javascript">
        $(document).ready(function() {

            const nameFilter = $('#nameFilter');
            const emailFilter = $('#emailFilter');
            const roleFilter = $('#roleFilter');
            const statusFilter = $('#statusFilter');
            const startDateFilter = $('#startDateFilter');
            const endDateFilter = $('#endDateFilter');

            const dbTableAjax = $("#userTable").DataTable({
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
                        d.email = emailFilter.val();
                        d.status = statusFilter.val();
                        d.role_id = roleFilter.val();
                        d.start_date = startDateFilter.val();
                        d.end_date = endDateFilter.val();
                    },
                },
                columns: [{
                        data: null, // Render checkbox for bulk actions
                        orderable: false,
                        searchable: false,
                        width: '10px',
                        render: function(data, type, row) {
                            return `<input type="checkbox" class="bulk-select" data-id="${row.id}" />`;
                        },
                    },

                    {
                        data: 'name',
                        name: 'name',
                        searchable: true,
                        orderable: true
                    },
                    {
                        data: 'email',
                        name: 'email',
                        searchable: true,
                        orderable: true
                    },
                    {
                        data: 'role_name',
                        name: 'role_name',
                        searchable: false,
                        orderable: true
                    },
                    {
                        data: 'status',
                        name: 'status',
                        searchable: false,
                        orderable: true
                    },
                    {
                        data: 'created_at',
                        name: 'created_at',
                        searchable: true,
                        orderable: true
                    },
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
                emailFilter.val('');
                roleFilter.val('');
                statusFilter.val('');
                startDateFilter.val('');
                endDateFilter.val('');
                // Reset the DataTable's search and reload
                dbTableAjax.ajax.reload();
            });


            $('#filterBtn').click(function() {
                dbTableAjax.ajax.reload();
                const filterSidebar = document.getElementById("filterSidebar");
                filterSidebar.classList.remove('show');
            });

            // "Select All" functionality
            $('#select-all').on('click', function() {
                let isChecked = $(this).is(':checked');
                $('.bulk-select').prop('checked', isChecked);
            });

            // bulk delete

            $('#bulk-delete-btn').on('click', function() {
                let selectedIds = [];
                $('.bulk-select:checked').each(function() {
                    selectedIds.push($(this).data('id'));
                });

                if (selectedIds.length > 0) {
                    if (confirm('Are you sure you want to delete the selected users?')) {
                        $.ajax({
                            url: "{{ route(getPanelRoutes($module . '.bulkDelete')) }}",
                            method: "POST",
                            data: {
                                ids: selectedIds,
                                _token: '{{ csrf_token() }}' // CSRF token for security
                            },
                            success: function(response) {
                                alert(response.message);
                                dbTableAjax.ajax.reload(); // Reload DataTable
                            },
                            error: function(error) {
                                alert('An error occurred while deleting users.');
                            }
                        });
                    }
                } else {
                    alert('No users selected for deletion.');
                }
            });

        });

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
