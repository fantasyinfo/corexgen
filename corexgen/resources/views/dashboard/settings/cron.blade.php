@extends('dashboard.settings.settings-layout')

@push('style')
<style>
    :root {
        --animation-duration: 0.3s;
    }

    .settings-card {
        transition: transform var(--animation-duration), box-shadow var(--animation-duration);
        background: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: 0.75rem;
    }

    .settings-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }

    .path-item {
        transition: all var(--animation-duration);
        border: 1px solid var(--border-color);
        border-radius: 0.5rem;
    }

    .path-item:hover {
        background-color: var(--body-bg);
    }

    .path-item.selected {
        border-color: var(--primary-color);
        background-color: var(--body-bg);
    }

    .version-badge {
        background-color: var(--primary-color);
        color: white;
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
        font-size: 0.875rem;
    }

    .environment-info {
        background-color: var(--body-bg);
        border-radius: 0.5rem;
        padding: 1rem;
    }

    .command-block {
        background-color: var(--body-bg);
        border: 1px solid var(--border-color);
        border-radius: 0.5rem;
        transition: all var(--animation-duration);
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .fade-in {
        animation: fadeIn 0.5s ease-out forwards;
    }
</style>
@endpush

@section('settings_content')
<div class="container-fluid px-4 py-5 fade-in">
    @php
        $phpInfo = find_php_paths();
        $bestPath = get_best_php_path($phpInfo, '8.1');
        $displayPaths = format_php_paths_for_display($phpInfo);
        $environment = $phpInfo['environment'];
    @endphp

    <div class="d-flex align-items-center gap-3 mb-4">
        <i class="bi bi-gear-fill fs-4 text-primary"></i>
        <h3 class="mb-0">Cron Job Settings</h3>
    </div>

    @if ($environment['restrictions']['can_execute_shell'] === false)
        <div class="alert alert-warning d-flex align-items-center gap-2 mb-4">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <div>
                <strong>Limited Environment Detected:</strong> Shell execution is disabled on this server. Some features may not work as expected.
            </div>
        </div>
    @endif

    <!-- Environment Information Card -->
    <div class="settings-card mb-4 p-4">
        <div class="d-flex align-items-center gap-2 mb-3">
            <i class="bi bi-info-circle-fill text-primary"></i>
            <h5 class="mb-0">System Environment</h5>
        </div>

        <div class="environment-info">
            <div class="row g-3">
                <div class="col-md-6 col-lg-4">
                    <strong>Operating System:</strong>
                    <div class="text-muted">{{ $environment['os'] }}</div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <strong>Server Software:</strong>
                    <div class="text-muted">{{ $environment['server_software'] ?: 'Not detected' }}</div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <strong>Control Panel:</strong>
                    <div class="text-muted">{{ ucfirst($environment['control_panel'] ?: 'None detected') }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- PHP Paths Card -->
    <div class="settings-card mb-4 p-4">
        <div class="d-flex align-items-center gap-2 mb-3">
            <i class="bi bi-terminal-fill text-primary"></i>
            <h5 class="mb-0">Available PHP Installations</h5>
        </div>

        @if (!empty($displayPaths))
            <div class="paths-container">
                <p class="mb-3">Select the PHP path for your cron jobs. The recommended path is pre-selected:</p>
                <div class="d-flex flex-column gap-3">
                    @foreach ($displayPaths as $pathInfo)
                        <div class="path-item p-3 {{ $pathInfo['path'] === $bestPath ? 'selected' : '' }}" 
                             data-path="{{ $pathInfo['path'] }}">
                            <div class="form-check d-flex align-items-center gap-3">
                                <input type="radio" 
                                       class="form-check-input php-path-radio" 
                                       id="php_path_{{ $loop->index }}" 
                                       name="php_bin_path" 
                                       value="{{ $pathInfo['path'] }}"
                                       {{ $pathInfo['path'] === $bestPath ? 'checked' : '' }}>
                                <label class="form-check-label w-100" for="php_path_{{ $loop->index }}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="font-monospace">{{ $pathInfo['path'] }}</span>
                                        <span class="version-badge">PHP {{ $pathInfo['version'] }}</span>
                                    </div>
                                    <div class="small text-muted mt-1">
                                        Last modified: {{ $pathInfo['last_modified'] }} | 
                                        Size: {{ $pathInfo['size_formatted'] }} | 
                                        Permissions: {{ $pathInfo['permissions'] }}
                                        @if (!$pathInfo['is_cli_available'])
                                            <span class="text-danger">| CLI Not Available</span>
                                        @endif
                                    </div>
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <div class="alert alert-danger d-flex align-items-center gap-2">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <div>
                    <strong>No PHP installations detected.</strong> Please ensure PHP is installed and accessible on your server.
                    @if ($environment['restrictions']['disable_functions'])
                        <div class="mt-2">
                            <small>Disabled functions: {{ $environment['restrictions']['disable_functions'] }}</small>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>

    <!-- Cron Command Card -->
    <div class="settings-card mb-4 p-4">
        <div class="d-flex align-items-center gap-2 mb-3">
            <i class="bi bi-clock-fill text-primary"></i>
            <h5 class="mb-0">Cron Job Command</h5>
        </div>

        <p>Your configured cron command with the selected PHP path:</p>

        <div class="command-block p-3 mb-3">
            <code id="cronCommand">* * * * * <span class="php-path-highlight">{{ $bestPath }}</span> -d register_argc_argv=On {{ base_path() }}/artisan schedule:run >> /dev/null 2>&1</code>
        </div>

        <div class="d-flex gap-3">
            <button class="btn btn-primary copy-btn d-flex align-items-center gap-2" 
                    onclick="copyCommand()">
                <i class="bi bi-clipboard"></i>
                <span id="copyText">Copy Command</span>
            </button>
        </div>
    </div>

   
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize with default path
    const defaultPath = '{{ $bestPath }}';
    updateCommand(defaultPath);

    // Add event listeners to all radio buttons
    document.querySelectorAll('.php-path-radio').forEach(radio => {
        radio.addEventListener('change', function() {
            updateCommand(this.value);
            updateSelectedStyles(this);
        });
    });

    // Intersection Observer for animations
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in');
            }
        });
    });

    document.querySelectorAll('.settings-card').forEach((card) => {
        observer.observe(card);
    });
});

function updateCommand(phpPath) {
    const pathSpan = document.querySelector('.php-path-highlight');
    pathSpan.textContent = phpPath;
    pathSpan.style.animation = 'none';
    pathSpan.offsetHeight;
    pathSpan.style.animation = 'fadeIn 0.3s ease-out';
}

function updateSelectedStyles(selectedRadio) {
    document.querySelectorAll('.path-item').forEach(item => {
        item.classList.remove('selected');
    });
    selectedRadio.closest('.path-item').classList.add('selected');
}

function copyCommand() {
    const command = document.getElementById('cronCommand').innerText;
    navigator.clipboard.writeText(command);
    
    const copyText = document.getElementById('copyText');
    const btn = copyText.parentElement;
    
    copyText.innerText = 'Copied!';
    btn.classList.remove('btn-primary');
    btn.classList.add('btn-success');
    
    setTimeout(() => {
        copyText.innerText = 'Copy Command';
        btn.classList.remove('btn-success');
        btn.classList.add('btn-primary');
    }, 2000);
}


</script>
@endpush