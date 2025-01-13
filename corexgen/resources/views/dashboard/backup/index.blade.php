@extends('layout.app')

@push('style')
    <style>
        .backup-section {
            background-color: var(--card-bg);
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
        }

        .backup-card {
            background-color: var(--card-bg);
            border-radius: 10px;
            border-left: 4px solid var(--info-color);
            transition: all 0.3s ease;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .backup-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .backup-icon {
            color: var(--info-color);
            font-size: 2.5rem;
            margin-right: 20px;
        }

        .btn-create-backup {
            background-color: var(--success-color);
            border-color: var(--success-color);
            color: var(--light-color);
            transition: all 0.3s ease;
        }

        .btn-create-backup:hover {
            background-color: var(--success-color);
            transform: scale(1.05);
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: var(--light-color);
        }

        .btn-primary:hover {
            background-color: var(--primary-hover);
        }

        .text-muted {
            color: var(--neutral-gray) !important;
        }

        .text-primary {
            color: var(--primary-color) !important;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid py-4">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="backup-section">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="mb-0">
                            <i class="fas fa-archive me-2 text-primary"></i>
                            Backup Management
                        </h2>

                        @if (hasPermission('BACKUP.CREATE'))
                            <form method="POST" action="{{ route(getPanelRoutes($module . '.createBackup')) }}">
                                @csrf
                                <button type="submit" id="createBackupBtn" class="btn btn-outline-primary">
                                    <i class="fas fa-plus-circle me-2"></i>
                                    Create New Backup
                                </button>
                            </form>
                        @endif
                    </div>

                    @if (hasPermission('DOWNLOAD_BACKUP.READ_ALL') || hasPermission('DOWNLOAD_BACKUP.READ'))
                        @forelse ($backupFiles as $backup)
                            <div class="card backup-card">
                                <div class="card-body d-flex align-items-center">
                                    <div class="backup-icon">
                                        <i class="fas fa-file-archive"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h5 class="mb-1">{{ $backup['filename'] }}</h5>
                                        <div class="text-muted small">
                                            <span class="me-3">
                                                <i class="fas fa-calendar-alt me-1"></i>
                                                {{ formatDateTime($backup['date']) }}
                                            </span>
                                            <span>
                                                <i class="fas fa-weight me-1"></i>
                                                {{ $backup['size'] }}
                                            </span>
                                        </div>
                                    </div>
                                    <div>
                                        <a href="{{ route(getPanelRoutes($module . '.download'), ['path' => $backup['path']]) }}"
                                            class="btn btn-primary btn-sm">
                                            <i class="fas fa-download me-1"></i>
                                            Download
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-5 bg-white rounded">
                                <i class="fas fa-archive fa-4x text-muted mb-3"></i>
                                <h3 class="text-muted">No Backups Found</h3>
                                <p class="text-secondary">
                                    Create a new backup to get started.
                                </p>
                            </div>
                        @endforelse
                    @else
                        <div class="alert alert-danger" role="alert">
                            <i class="fas fa-lock me-2"></i>
                            Insufficient permissions to view backups.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
