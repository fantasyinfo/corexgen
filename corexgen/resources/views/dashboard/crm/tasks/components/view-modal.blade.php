@push('style')
    <style>
        #viewLeadModal .modal-header {
            border-bottom: none;
            padding-bottom: 0;
        }

        #viewLeadModal .action-menu {
            position: absolute;
            right: 15px;
            top: 70px;
            width: 200px;
            background: var(--body-bg);
            border: 1px solid var(--border-bg);
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }

        #viewLeadModal .action-menu-item {
            padding: 10px 15px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--body-color);
            text-decoration: none;
            transition: background-color 0.2s;
        }

        #viewLeadModal .action-menu-item:hover {
            background-color: #f7fafc;
            color: var(--primary-color);
        }

        #viewLeadModal .avatar-group {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        #viewLeadModal .avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            border: 2px solid white;
            margin-left: -8px;
        }

        #viewLeadModal .avatar:first-child {
            margin-left: 0;
        }



        #viewLeadModal .add-member {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #e2e8f0;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        #viewLeadModal .add-member:hover {
            background-color: #cbd5e0;
        }

        #viewLeadModal .status-badge {
            background-color: #fbd38d;
            color: #744210;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        #viewLeadModal .status-badge:hover {
            background-color: #f6ad55;
        }

        #viewLeadModal .task-label {
            background-color: #e2e8f0;
            padding: 5px 15px;
            border-radius: 15px;
            font-size: 12px;
            margin-right: 8px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        #viewLeadModal .task-label i {
            cursor: pointer;
            opacity: 0.6;
        }

        #viewLeadModal .task-label i:hover {
            opacity: 1;
        }

       
        #viewLeadModal .body-bg {
            background-color: var(--body-bg);
        }
    </style>
@endpush

<div class="modal fade" id="viewLeadModal" tabindex="-1" aria-labelledby="viewLeadModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <div class="d-flex align-items-center">
                    <h5 class="modal-title mb-0" id="title">Tasks Title</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body body-bg">


                <div class="mt-4">

                    @include('dashboard.crm.tasks.components.modal._detailsShow')

                    @include('dashboard.crm.tasks.components.modal._assignees')

                </div>
                
            </div>
            <div class="modal-footer">
                <a href="" id="details_view_link" class="mw-3 btn btn-sm btn-primary">Details view</a>
            </div>
        </div>
    </div>
</div>
</div>

@push('scripts')
    <script>
        $(document).ready(function() {


            // Action menu toggle
            $("#actionMenuBtn").click(function(e) {
                e.stopPropagation();
                $(".action-menu").toggleClass("d-none");
            });

            // Close action menu when clicking outside
            $(document).click(function() {
                $(".action-menu").addClass("d-none");
            });

            // Prevent modal from closing when clicking inside
            $(".action-menu").click(function(e) {
                e.stopPropagation();
            });

            // Status badge click handler
            $(".status-badge").click(function() {
                // Add status change functionality here
                console.log("Status clicked");
            });


        });
    </script>
@endpush