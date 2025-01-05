@extends('layout.app')

@push('style')
    <style>
        .template-card {
            background-color: var(--card-bg);
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            margin: 1.5rem 0;
        }

        .template-card-header {
            padding: 1.25rem;
            border-bottom: 1px solid var(--body-bg);
            background-color: var(--card-bg);
            border-top-left-radius: 0.5rem;
            border-top-right-radius: 0.5rem;
        }

        .template-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--primary-color);
        }

        .status-badge {
            display: inline-block;
            padding: 0.375rem 0.75rem;
            background-color: #dbeafe;
            color: #1e40af;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .template-content {
            padding: 1.5rem;
        }

        .content-section {
            margin-bottom: 1.5rem;
        }

        .content-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--body-color);
            margin-bottom: 0.5rem;
        }

        .content-box {
            background-color: var(--card-bg);
            border: 1px solid var(--body-bg);
            border-radius: 0.375rem;
            padding: 1rem;
            color: var(--body-color);
        }

        .template-footer {
            padding: 1rem 1.5rem;
            background-color: var(--card-bg);
            border-top: 1px solid var(--body-bg);
            border-bottom-left-radius: 0.5rem;
            border-bottom-right-radius: 0.5rem;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid py-4">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="template-card">
                    <div class="template-card-header d-flex justify-content-between align-items-center">
                        <h2 class="template-title mb-0">{{ $type }} Template</h2>
                        <span class="status-badge">
                            {{ $template->status ?? 'Active' }}
                        </span>
                    </div>

                    <div class="template-content">
                        <div class="content-section">
                            <x-form-components.input-label for="t" class="content-label">
                                {{ __('templates.Title') }}
                            </x-form-components.input-label>
                            <div class="content-box">
                                {{ $template->title }}
                            </div>
                        </div>

                        <div class="content-section">
                            <x-form-components.input-label for="d" class="content-label">
                                {{ __('templates.Description') }}
                            </x-form-components.input-label>
                            <div class="content-box">
                                {!! $template->template_details !!}
                            </div>
                        </div>
                    </div>

                    <div class="template-footer">
                        <div class="d-flex justify-content-end align-items-center">
                            {{-- Add action buttons here if needed --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    {{-- Add any necessary JavaScript here --}}
@endpush
