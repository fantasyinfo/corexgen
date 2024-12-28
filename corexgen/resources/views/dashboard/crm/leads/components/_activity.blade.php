<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0">Activity Timeline</h5>

</div>

<div class="timeline-wrapper">
    @if ($activities && $activities->isNotEmpty())
        <div class="timeline">
            @foreach ($activities as $activity)
                <div class="timeline-item">
                    <!-- Activity Icon -->
                    <div class="timeline-icon">
                        @php
                            // Define activity type based on changes
                            $iconClass = 'fa-edit';
                            $iconBg = 'bg-primary';

                            // Check what fields were changed to determine activity type
                            $changes = array_keys($activity['new_values']);
                            if (in_array('status', $changes)) {
                                $iconClass = 'fa-flag';
                                $iconBg = 'bg-warning';
                            } elseif (in_array('score', $changes)) {
                                $iconClass = 'fa-star';
                                $iconBg = 'bg-info';
                            } elseif (in_array('is_converted', $changes)) {
                                $iconClass = 'fa-check-circle';
                                $iconBg = 'bg-success';
                            }
                        @endphp
                        <div class="icon-wrapper {{ $iconBg }}">
                            <i class="fas {{ $iconClass }}"></i>
                        </div>
                    </div>

                    <!-- Activity Content -->
                    <div class="timeline-content-wrapper">
                        <div class="timeline-content">
                            <!-- Activity Header -->
                            <div class="activity-header">
                                <div class="activity-title">
                                    <h6>{{ $activity['user']['name'] ?? 'Unknown User' }}
                                    </h6>
                                    <span class="activity-time">
                                        <i class="far fa-clock me-1"></i>
                                        {{ $activity['created_at'] ? \Carbon\Carbon::parse($activity['created_at'])->diffForHumans() : 'N/A' }}
                                    </span>
                                </div>
                            </div>

                            <!-- Activity Changes -->
                            <!-- Activity Changes -->
                            <div class="activity-changes">
                                @foreach ($activity['new_values'] as $key => $newValue)
                                    @php
                                        $oldValue = $activity['old_values'][$key] ?? 'N/A';
                                        $fieldName = ucfirst(str_replace('_', ' ', $key));

                                        // Format special values
                                        $oldFormatted = is_array($oldValue) ? implode(', ', $oldValue) : $oldValue;
                                        $newFormatted = is_array($newValue) ? implode(', ', $newValue) : $newValue;

                                        // Format boolean values
                                        if (is_bool($oldValue) || $oldValue === '0' || $oldValue === '1') {
                                            $oldFormatted = filter_var($oldValue, FILTER_VALIDATE_BOOLEAN)
                                                ? 'Yes'
                                                : 'No';
                                        }
                                        if (is_bool($newValue) || $newValue === '0' || $newValue === '1') {
                                            $newFormatted = filter_var($newValue, FILTER_VALIDATE_BOOLEAN)
                                                ? 'Yes'
                                                : 'No';
                                        }
                                    @endphp
                                    <div class="change-item">
                                        <span class="field-label">{{ $fieldName }}</span>
                                        <div class="change-values">
                                            <span class="old-value">{{ $oldFormatted }}</span>
                                            <i class="fas fa-arrow-right mx-2"></i>
                                            <span class="new-value">{{ $newFormatted }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="empty-state">
            <div class="empty-state-icon">
                <i class="fas fa-history"></i>
            </div>
            <h6>No Activities Yet</h6>
            <p class="text-muted">Activities will appear here when changes are made to the
                lead.</p>
        </div>
    @endif
</div>
