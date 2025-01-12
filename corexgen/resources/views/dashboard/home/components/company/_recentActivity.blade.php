<!-- Recent Activity Table -->
<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">{{__("general.Recent Activities")}}</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Event</th>
                                    <th>Model</th>
                                    <th>Date</th>
                                    <th>Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($recentActivities as $activity)
                                    <tr>
                                        <td>{{ $activity->user ? $activity->user->name : 'System' }}</td>
                                        <td>{{ ucfirst($activity->event) }}</td>
                                        <td>{{ class_basename($activity->auditable_type) }}</td>
                                        <td>{{ $activity->created_at->format('M d, Y H:i') }}</td>
                                        <td>
                                            <a href="#" class="btn btn-sm btn-primary" data-bs-toggle="offcanvas"
                                                data-bs-target="#activityOffcanvas"
                                                onclick="showActivityDetails(
                                                   '{{ $activity->user ? $activity->user->name : 'System' }}',
                                                   '{{ ucfirst($activity->event) }}',
                                                   '{{ class_basename($activity->auditable_type) }}',
                                                   '{{ $activity->created_at->format('M d, Y H:i') }}',
                                                   '{{ json_encode($activity->old_values) }}',
                                                   '{{ json_encode($activity->new_values) }}',
                                                   '{{ $activity->ip_address }}',
                                                   '{{ $activity->user_agent }}'
                                               )">
                                                View
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap Offcanvas -->
<div class="filter-sidebar offcanvas offcanvas-end" tabindex="-1" id="activityOffcanvas"
    aria-labelledby="activityOffcanvasLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="activityOffcanvasLabel">Activity Details</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <!-- Dynamic Content -->
        <p><strong>User:</strong> <span id="activityUser"></span></p>
        <p><strong>Event:</strong> <span id="activityEvent"></span></p>
        <p><strong>Model:</strong> <span id="activityModel"></span></p>
        <p><strong>Date:</strong> <span id="activityDate"></span></p>
        <p><strong>Old Values:</strong> <span id="activityOldValues"></span></p>
        <p><strong>New Values:</strong> <span id="activityNewValues"></span></p>
        <p><strong>IP Address:</strong> <span id="activityIpAddress"></span></p>
        <p><strong>User Agent:</strong> <span id="activityUserAgent"></span></p>
    </div>
</div>

@push('scripts')
    <script>
        // JavaScript Function to Populate Offcanvas
        function showActivityDetails(user, event, model, date, oldValues, newValues, ipAddress, userAgent) {
            document.getElementById('activityUser').innerText = user;
            document.getElementById('activityEvent').innerText = event;
            document.getElementById('activityModel').innerText = model;
            document.getElementById('activityDate').innerText = date;
            document.getElementById('activityIpAddress').innerText = ipAddress;
            document.getElementById('activityUserAgent').innerText = userAgent;

            // Format Old Values
            const oldValuesContainer = document.getElementById('activityOldValues');
            const oldValuesJson = JSON.parse(oldValues);
            oldValuesContainer.innerHTML = formatKeyValuePairs(oldValuesJson);

            // Format New Values
            const newValuesContainer = document.getElementById('activityNewValues');
            const newValuesJson = JSON.parse(newValues);
            newValuesContainer.innerHTML = formatKeyValuePairs(newValuesJson);
        }

        function formatKeyValuePairs(data) {
            let html = '<ul>';
            for (const key in data) {
                html += `<li><strong>${key}:</strong> ${data[key]}</li>`;
            }
            html += '</ul>';
            return html;
        }
    </script>
@endpush
