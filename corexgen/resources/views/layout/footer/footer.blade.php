<footer class="footer text-center py-3 shadow-sm">
    &copy; {{date('Y')}} {{config('app.name')}} 
    <span class="text-success">v{{config('app.version')}}</span> | 
    <span class="text-muted">
        @php
            $timezone = getSettingValue('Time Zone') ?: config('app.timezone');
            $dateFormat = getSettingValue('Date Format') ?: 'd M Y, h:i A';
            $initialTime = now()->setTimezone($timezone);
        @endphp
        <span id="live-datetime">{{ $initialTime->format($dateFormat) }}</span>
        <span>({{ $timezone }})</span>
    </span>.
    All rights reserved.
</footer>

<script>
function updateClock() {
    // Get the server-provided timezone
    const timezone = @json($timezone);
    const format = @json($dateFormat);
    
    // Create a date object
    const now = new Date();
    
    // Format the date using the server's format
    let formattedDate = '';
    try {
        // Using moment.js for consistent formatting with server
        formattedDate = moment().tz(timezone).format(format
            .replace('d', 'DD')
            .replace('M', 'MMM')
            .replace('Y', 'YYYY')
            .replace('h', 'hh')
            .replace('i', 'mm')
            .replace('A', 'A')
        );
    } catch (e) {
        // Fallback if moment.js isn't available
        formattedDate = now.toLocaleString('en-US', { 
            timeZone: timezone,
            year: 'numeric',
            month: 'short',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit',
            hour12: true
        });
    }
    
    // Update the element
    document.getElementById('live-datetime').textContent = formattedDate;
}

// Update immediately and then every second
updateClock();
setInterval(updateClock, 1000);
</script>