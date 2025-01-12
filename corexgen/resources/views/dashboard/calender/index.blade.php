@extends('layout.app')

@push('style')
    <style>
        #calendar {
            max-width: 1100px;
            margin: auto;
        }

        .event-priority-high {
            border-left: 3px solid #dc3545 !important;
        }

        .event-priority-medium {
            border-left: 3px solid #ffc107 !important;
        }

        .event-priority-low {
            border-left: 3px solid #28a745 !important;
        }

        .event-status-completed {
            opacity: 0.7;
        }

        .event-recurring::after {
            content: '↻';
            position: absolute;
            right: 3px;
            top: 3px;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div id="calendar"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Event Modal -->
    <div class="modal fade" id="eventModal" tabindex="-1" aria-labelledby="eventModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="eventModalLabel">Event Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Details will be loaded here -->
                </div>
                <div class="modal-footer d-flex justify-content-between align-items-center bg-light border-top">
                    <div class="d-flex gap-2">
                        @if (isset($permissions['UPDATE']) && hasPermission(strtoupper($module) . '.' . $permissions['UPDATE']['KEY']))
                            <a href="" class="btn btn-warning d-flex align-items-center gap-1" id="event_id_edit">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                        @endif
                        @if (isset($permissions['DELETE']) && hasPermission(strtoupper($module) . '.' . $permissions['DELETE']['KEY']))
                            <form method="POST" id="event_id_delete" action="" class="d-inline-block">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="btn btn-danger d-flex align-items-center gap-1"
                                    id="confirm_delete">
                                    <i class="fas fa-trash-alt"></i> Delete
                                </button>
                            </form>
                        @endif
                    </div>
                    <button type="button" class="btn btn-secondary d-flex align-items-center gap-1"
                        data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Close
                    </button>
                </div>


            </div>
        </div>
    </div>

    <div class="modal fade" id="successEventModal" tabindex="-1" role="dialog" aria-labelledby="successEventModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="successModalLabel">Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection


@push('scripts')
    <script src="{{ asset('js/calender/calender.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');

            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                events: {
                    url: '{{ route(getPanelRoutes($module . '.fetch')) }}',
                    method: 'GET',
                    failure: function() {
                        alert('Error fetching events!');
                    }
                },
                editable: true,
                selectable: true,
                selectHelper: true,

                // Allow event creation on date selection
                select: function(info) {
                    window.location.href = '{{ route(getPanelRoutes($module . '.create')) }}?date=' +
                        info.startStr;
                },

                // Show event details on click
                eventClick: function(info) {
                    let baseUrl =
                        "{{ route(getPanelRoutes($module . '.view'), ['id' => ':id']) }}";
                    let url = baseUrl.replace(':id', info.event.id);


                    let baseEditUrl =
                        "{{ route(getPanelRoutes($module . '.edit'), ['id' => ':id']) }}";
                    let urlEdit = baseEditUrl.replace(':id', info.event.id);

                    $("#event_id_edit").attr("href", urlEdit)

                    let baseDelUrl =
                        "{{ route(getPanelRoutes($module . '.destroy'), ['id' => ':id']) }}";
                    let urlDel = baseDelUrl.replace(':id', info.event.id);


                    $("#event_id_delete").attr("action", urlDel)

                    $.ajax({
                        url: url,
                        method: 'GET',
                        success: function(response) {
                            let event = response
                                .event; // Assuming the event data is returned in `response.event`
                            let modalContent = `
                <div class="container">
                    <div class="event-header text-center mb-4">
                        <h4><i class="fas fa-calendar-alt"></i> ${event.title}</h4>
                        <span class="badge bg-${
                          event.priority === 'high' ? 'danger' : 'primary'
                        }">${event.priority.toUpperCase() || 'NORMAL'}</span>
                        <span class="badge bg-${
                          event.status === 'upcoming' ? 'success' : 'secondary'
                        }">${event.status.toUpperCase()}</span>
                    </div>
                    <table class="table table-borderless">
                        <tbody>
                            <tr>
                                <th><i class="fas fa-info-circle"></i> Description</th>
                                <td>${event.description || 'N/A'}</td>
                            </tr>
                            <tr>
                                <th><i class="fas fa-clock"></i> Start Date</th>
                                <td>${new Date(event.start_date).toLocaleString() || 'N/A'}</td>
                            </tr>
                            <tr>
                                <th><i class="fas fa-clock"></i> End Date</th>
                                <td>${event.end_date ? new Date(event.end_date).toLocaleString() : 'N/A'}</td>
                            </tr>
                            <tr>
                                <th><i class="fas fa-map-marker-alt"></i> Location</th>
                                <td>${event.location || 'N/A'}</td>
                            </tr>
                            <tr>
                                <th><i class="fas fa-link"></i> Meeting Link</th>
                                <td>${event.meeting_link ? `<a href="${event.meeting_link}" target="_blank">${event.meeting_link}</a>` : 'N/A'}</td>
                            </tr>
                            <tr>
                                <th><i class="fas fa-globe"></i> Timezone</th>
                                <td>${event.timezone || 'N/A'}</td>
                            </tr>
                            <tr>
                                <th><i class="fas fa-user"></i> Creator</th>
                                <td>
                                    <img src="${
                                      event.creator?.profile_photo_url || ''
                                    }" alt="Avatar" class="rounded-circle" width="30">
                                    ${event.creator?.name || 'N/A'} (${event.creator?.email || 'N/A'})
                                </td>
                            </tr>
                            <tr>
                                <th><i class="fas fa-building"></i> Company</th>
                                <td>${event.company?.name || 'N/A'} (${event.company?.email || 'N/A'})</td>
                            </tr>
                        </tbody>
                    </table>
                
                </div>
            `;
                            $('#eventModal .modal-body').html(modalContent);
                            $('#eventModal').modal('show');
                        }
                    });
                },

                // Handle event drag & drop
                eventDrop: function(info) {
                    if (!confirm("Are you sure about this change?")) {
                        info.revert();
                        return;
                    }

                    updateEvent(info.event);
                },

                // Handle event resize
                eventResize: function(info) {
                    if (!confirm("Are you sure about this change?")) {
                        info.revert();
                        return;
                    }

                    updateEvent(info.event);
                }
            });

            calendar.render();

            function updateEvent(event) {
                // Format the dates to ISO 8601 string
                let startDate = new Date(event.start).toISOString(); // Convert to ISO format
                let endDate = event.end ?
                    new Date(event.end).toISOString() :
                    new Date(new Date(event.start).getTime() + 60 * 60 * 1000).toISOString();

                $.ajax({
                    url: '{{ route(getPanelRoutes($module . '.update')) }}',
                    method: 'PUT',
                    data: {
                        id: event.id,
                        start_date: startDate,
                        end_date: endDate,
                        title: event.title,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#successEventModal .modal-body').html(response.message);
                            $('#successEventModal').modal('show');
                        } else {
                            $('#successEventModal .modal-body').html(response.message);
                            $('#successEventModal').modal('show');
                            calendar.refetchEvents();
                        }
                    },
                    error: function(xhr) {
                        let modalMessage = '<ul>';

                        // Check if the response contains JSON and errors
                        if (xhr.responseJSON && xhr.responseJSON.errors) {
                            let errors = xhr.responseJSON.errors;
                            for (const [field, messages] of Object.entries(errors)) {
                                messages.forEach(function(msg) {
                                    modalMessage +=
                                        `<li>${msg}</li>`; // Add each error message as a list item
                                });
                            }
                            modalMessage += '</ul>';
                        } else {
                            // Fallback message for unexpected errors
                            modalMessage =
                                '<p>An unexpected error occurred while updating the event.</p>';
                        }

                        // Display the errors in the modal
                        $('#successEventModal .modal-body').html(modalMessage);
                        $('#successEventModal').modal('show');

                        // Refresh calendar events
                        calendar.refetchEvents();
                    }

                });
            }

        });

        document.getElementById('confirm_delete').addEventListener('click', function(e) {
            e.preventDefault(); // Prevent form submission

            // Show confirmation dialog
            if (confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
                // Submit the form if confirmed
                document.getElementById('event_id_delete').submit();
            }
        });
    </script>
@endpush
