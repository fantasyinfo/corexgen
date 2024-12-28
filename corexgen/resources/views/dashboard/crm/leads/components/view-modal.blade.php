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

        /* #viewLeadModal .tab-container {
                                border-bottom: 1px solid #e2e8f0;
                                margin-bottom: 20px;
                            }

                            #viewLeadModal .tab-button {
                                border: none;
                                background: none;
                                padding: 12px 24px;
                                font-size: 14px;
                                color: #4a5568;
                                border-bottom: 2px solid transparent;
                                transition: all 0.2s;
                            }

                            #viewLeadModal .tab-button:hover {
                                color: #2c5282;
                            }

                            #viewLeadModal .tab-button.active {
                                color: #2c5282;
                                border-bottom: 2px solid #2c5282;
                                font-weight: 600;
                            }

                            #viewLeadModal .tab-content {

                                padding: 20px 0;
                            }

                            #viewLeadModal .tab-content.active {
                                display: block;
                            }

                            #viewLeadModal .checklist-item {
                                display: flex;
                                align-items: center;
                                gap: 10px;
                                padding: 10px 0;
                                border-bottom: 1px solid #e2e8f0;
                            }

                            #viewLeadModal .comment-box {
                                border: 1px solid #e2e8f0;
                                border-radius: 8px;
                                padding: 15px;
                                margin-bottom: 20px;
                            }

                            #viewLeadModal .comment-input {
                                border: none;
                                width: 100%;
                                resize: none;
                                margin-bottom: 10px;
                            }

                            #viewLeadModal .comment-input:focus {
                                outline: none;
                            }

                            #viewLeadModal .file-item {
                                display: flex;
                                align-items: center;
                                gap: 15px;
                                padding: 12px;
                                border: 1px solid #e2e8f0;
                                border-radius: 8px;
                                margin-bottom: 10px;
                            }

                            #viewLeadModal .file-icon {
                                width: 40px;
                                height: 40px;
                                background-color: #ebf8ff;
                                border-radius: 8px;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                color: #2c5282;
                            }

                            #viewLeadModal .activity-item {
                                display: flex;
                                gap: 15px;
                                margin-bottom: 20px;
                                padding-bottom: 20px;
                                border-bottom: 1px solid #e2e8f0;
                            }

                            #viewLeadModal .activity-content {
                                flex: 1;
                            }

                            #viewLeadModal .private-badge {
                                background-color: #e6fffa;
                                color: #234e52;
                                padding: 8px 12px;
                                border-radius: 6px;
                                font-size: 14px;
                                margin-bottom: 20px;
                                display: inline-flex;
                                align-items: center;
                                gap: 8px;
                            }

                            #viewLeadModal .date-badge {
                                background-color: #f7fafc;
                                padding: 4px 8px;
                                border-radius: 4px;
                                font-size: 12px;
                                color: #4a5568;
                            }*/
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
                    <span class="badge bg-secondary me-2">8/12</span>
                    <h5 class="modal-title mb-0" id="title">Lead Title</h5>
              
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body body-bg">


                <div class="mt-4">

                    @include('dashboard.crm.leads.components.modal._detailsShow')

                    @include('dashboard.crm.leads.components.modal._assignees')

                </div>
                
            </div>
            <div class="modal-footer">
                <a href="" class="mw-3 btn btn-sm btn-outline-primary">Details view</a>
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
