@extends('layout.app')

@push('style')
    <style>
        .audit-card {
            background-color: var(--card-bg);
            border-color: var(--border-color);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .audit-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .event-badge {
            text-transform: uppercase;
            font-weight: bold;
            padding: 0.3rem 0.6rem;
        }

        .event-created {
            background-color: var(--success-color);
            color: white;
        }

        .event-updated {
            background-color: var(--warning-color);
            color: var(--dark-color);
        }

        .event-deleted {
            background-color: var(--danger-color);
            color: white;
        }

        .audit-details-table {
            font-size: 0.9rem;
        }

        .card-header {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-filter {
            background-color: var(--light-color);
            color: var(--body-color);
            border-color: var(--border-color);
            margin: 0 0.25rem;
        }

        .btn-filter.active {
            background-color: var(--primary-color);
            color: white;
        }

        .modal-content {
            background-color: var(--card-bg);
        }

        .modal-header {
            border-bottom-color: var(--border-color);
        }

        .modal-footer {
            border-top-color: var(--border-color);
        }

        pre {
            background-color: var(--input-bg) !important;
            color: var(--body-color);
            border: 1px solid var(--input-border);
        }

        .modal-diff-container {
            display: flex;
            gap: 1rem;
            max-height: 500px;
        }

        .modal-diff-container>div {
            flex: 1;
            position: relative;
        }

        .values-container {
            background-color: var(--input-bg);
            border: 1px solid var(--input-border);
            border-radius: 0.375rem;
            padding: 1rem;
            max-height: 400px;
            overflow-y: auto;
            font-family: 'Courier New', monospace;
            font-size: 0.85rem;
            line-height: 1.6;
            white-space: pre-wrap;
            word-break: break-all;
        }

        .values-container.old-values {
            background-color: rgba(255, 0, 0, 0.05);
        }

        .values-container.new-values {
            background-color: rgba(0, 255, 0, 0.05);
        }

        .values-container .diff-highlight {
            background-color: rgba(255, 0, 0, 0.2);
            text-decoration: line-through;
        }

        .values-container .diff-new {
            background-color: rgba(0, 255, 0, 0.2);
        }

        .modal-header {
            background-color: var(--primary-color);
            color: white;
        }

        .modal-header .btn-close {
            background-color: rgba(255, 255, 255, 0.2);
            opacity: 1;
            border-radius: 50%;
            padding: 0.5rem;
        }

        .modal-title {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .modal-diff-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
            color: var(--neutral-gray);
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-lg audit-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"> Audit Logs</h5>
                        <div class="btn-group" role="group">
                            <button class="btn btn-sm btn-filter" id="filterAll">All</button>
                            <button class="btn btn-sm btn-filter" id="filterCreated">Created</button>
                            <button class="btn btn-sm  btn-filter" id="filterUpdated">Updated</button>
                            <button class="btn btn-sm btn-filter" id="filterDeleted">Deleted</button>
                        </div>
                    </div>
                    <div class="card-body">
                        @if ($audits->isEmpty())
                            <div class="alert alert-info text-center" role="alert">
                                <i class="fas fa-info-circle me-2"></i>No audit logs found.
                            </div>
                        @else
                            <div class="row" id="auditLogsContainer">
                                @foreach ($audits as $audit)
                                    <div class="col-md-4 mb-4 audit-log-item" data-event="{{ $audit->event }}">
                                        <div class="card audit-card shadow-sm">
                                            <div class="card-header d-flex justify-content-between align-items-center">
                                                <span class="badge event-badge event-{{ $audit->event }}">
                                                    {{ $audit->event }}
                                                </span>
                                                <small class="text-secondary">
                                                    {{ $audit->created_at->diffForHumans() }}
                                                </small>
                                            </div>
                                            <div class="card-body">
                                                <h5 class="card-title">
                                                    <i class="fas fa-user-circle me-2"></i>
                                                    {{ optional($audit->user)->name ?? 'System' }}
                                                </h5>
                                                <div class="table-responsive">
                                                    <table class="table table-sm audit-details-table">
                                                        <tbody>
                                                            <tr>
                                                                <td><strong>Model:</strong></td>
                                                                <td>
                                                                    @php
                                                                        $type = class_basename($audit->auditable_type);
                                                                        switch ($type) {
                                                                            case 'CRMRole':
                                                                                $type = 'Role';
                                                                                break;
                                                                            case 'CRMSettings':
                                                                                $type = 'Settings';
                                                                                break;
                                                                            case 'CRMLeads':
                                                                                $type = 'Leads';
                                                                                break;
                                                                            case 'CRMProposals':
                                                                                $type = 'Proposals';
                                                                                break;
                                                                            case 'CRMClients':
                                                                                $type = 'Clients';
                                                                                break;
                                                                            default:
                                                                                $type = $type;
                                                                                break;
                                                                        }

                                                                    @endphp
                                                                    {{ $type }}
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td><strong>IP Address:</strong></td>
                                                                <td>{{ $audit->ip_address }}</td>
                                                            </tr>
                                                            <tr>
                                                                <td><strong>URL:</strong></td>
                                                                <td>
                                                                    <a href="{{ $audit->url }}" target="_blank"
                                                                        class="text-truncate d-block"
                                                                        style="max-width: 200px;">
                                                                        {{ $audit->url }}
                                                                    </a>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                            <div class="card-footer">
                                                <button class="btn btn-sm btn-outline-primary view-details"
                                                    data-bs-toggle="modal" data-bs-target="#auditDetailsModal"
                                                    data-old-values="{{ json_encode($audit->old_values) }}"
                                                    data-new-values="{{ json_encode($audit->new_values) }}">
                                                    <i class="fas fa-eye me-1"></i>View Details
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Audit Details Modal -->
    <div class="modal fade" id="auditDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-file-alt me-2"></i>
                        Audit Log Comparison
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="modal-diff-container">
                        <div>
                            <div class="modal-diff-header">
                                <h6>Old Values</h6>
                                <small id="oldValuesCount" class="text-muted"></small>
                            </div>
                            <div id="oldValuesContainer" class="values-container old-values"></div>
                        </div>
                        <div>
                            <div class="modal-diff-header">
                                <h6>New Values</h6>
                                <small id="newValuesCount" class="text-muted"></small>
                            </div>
                            <div id="newValuesContainer" class="values-container new-values"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Previous filter functionality remains the same
            const filterButtons = {
                all: document.getElementById('filterAll'),
                created: document.getElementById('filterCreated'),
                updated: document.getElementById('filterUpdated'),
                deleted: document.getElementById('filterDeleted')
            };

            const auditLogs = document.querySelectorAll('.audit-log-item');

            function filterAuditLogs(eventType) {
                // Remove active class from all buttons
                Object.values(filterButtons).forEach(btn => btn.classList.remove('active'));

                // Add active class to selected button
                filterButtons[eventType].classList.add('active');

                auditLogs.forEach(log => {
                    if (eventType === 'all' || log.dataset.event === eventType) {
                        log.style.display = '';
                    } else {
                        log.style.display = 'none';
                    }
                });
            }

            // Set initial state
            filterButtons.all.classList.add('active');

            Object.keys(filterButtons).forEach(key => {
                filterButtons[key].addEventListener('click', () => filterAuditLogs(key));
            });
            // Modal details view with enhanced diff
            const detailsButtons = document.querySelectorAll('.view-details');
            const oldValuesContainer = document.getElementById('oldValuesContainer');
            const newValuesContainer = document.getElementById('newValuesContainer');
            const oldValuesCount = document.getElementById('oldValuesCount');
            const newValuesCount = document.getElementById('newValuesCount');

            function formatJSON(obj) {
                return JSON.stringify(obj, null, 2);
            }

            function highlightDiffs(oldValues, newValues) {
                const oldObj = oldValues || {};
                const newObj = newValues || {};

                // Create a container for highlighted differences
                const diffContainer = document.createElement('div');

                // Compare and highlight differences
                Object.keys({
                    ...oldObj,
                    ...newObj
                }).forEach(key => {
                    const oldValue = oldObj[key];
                    const newValue = newObj[key];

                    // Check if the key exists in both objects and values are different
                    if (oldValue !== newValue) {
                        const diffLine = document.createElement('div');
                        diffLine.innerHTML = `<strong>${key}:</strong> `;

                        // Old value (crossed out)
                        if (oldValue !== undefined) {
                            const oldSpan = document.createElement('span');
                            oldSpan.classList.add('diff-highlight');
                            oldSpan.textContent = oldValue;
                            diffLine.appendChild(oldSpan);
                        }

                        // New value (highlighted)
                        if (newValue !== undefined) {
                            const newSpan = document.createElement('span');
                            newSpan.classList.add('diff-new');
                            newSpan.textContent = ` → ${newValue}`;
                            diffLine.appendChild(newSpan);
                        }

                        diffContainer.appendChild(diffLine);
                    }
                });

                return diffContainer.innerHTML || 'No changes detected';
            }

            detailsButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const oldValues = JSON.parse(this.dataset.oldValues);
                    const newValues = JSON.parse(this.dataset.newValues);

                    // Formatted full JSON view
                    oldValuesContainer.textContent = formatJSON(oldValues);
                    newValuesContainer.textContent = formatJSON(newValues);

                    // Diff view
                    const diffContent = highlightDiffs(oldValues, newValues);

                    // Update counts
                    oldValuesCount.textContent = `${Object.keys(oldValues || {}).length} fields`;
                    newValuesCount.textContent = `${Object.keys(newValues || {}).length} fields`;
                });
            });
        });
    </script>
@endpush
