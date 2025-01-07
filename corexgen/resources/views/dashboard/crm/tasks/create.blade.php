@extends('layout.app')

@section('content')
    @php
        //prePrintR($customFields->toArray());
    @endphp
    <div class="container">
        <div class="row">
            <div class="col-lg-9">
                <div class="card stretch stretch-full">
                    <form id="taskForm" action="{{ route(getPanelRoutes('tasks.store')) }}" method="POST">
                        @csrf
                        <div class="card-body">
                            <div class="mb-4 d-flex align-items-center justify-content-between">
                                <p class="fw-bold mb-0 me-4">
                                    <span class="d-block mb-2">{{ __('tasks.Create New Task') }}</span>
                                    <span
                                        class="fs-12 fw-normal text-muted text-truncate-1-line">{{ __('crud.Please add correct information') }}</span>
                                </p>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> <span>{{ __('tasks.Create Task') }}</span>
                                </button>
                            </div>

                            <!-- Bootstrap Tabs -->
                            <ul class="nav nav-tabs" id="clientsTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="general-tab" data-bs-toggle="tab"
                                        data-bs-target="#general" type="button" role="tab">
                                        {{ __('leads.General Information') }}
                                    </button>
                                </li>

                                @if (isset($customFields) && $customFields->isNotEmpty())
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="custom-fields-tab" data-bs-toggle="tab"
                                            data-bs-target="#custom-fields" type="button" role="tab">
                                            {{ __('customfields.Custom Fields') }}
                                        </button>
                                    </li>
                                @endif
                            </ul>

                            <div class="tab-content mt-4" id="clientsTabsContent">
                                <!-- General Information Tab -->
                                <div class="tab-pane fade show active" id="general" role="tabpanel">
                                    <div class="row mb-4">
                                        <div class="col-lg-4">
                                            <x-form-components.input-label for="billable">
                                                {{ __('tasks.Check') }}
                                            </x-form-components.input-label>
                                        </div>
                                        <div class="col-lg-8">

                                            <input type="checkbox" name="billable" id="billable" value="1"
                                                {{ old('billable') == '1' ? 'checked' : '' }}
                                                class="custom-class form-check-input" />
                                            <label class="me-2">Billable</label>

                                            <input type="checkbox" name="visible_to_client" id="visible_to_client"
                                                value="1" {{ old('visible_to_client') == '1' ? 'checked' : '' }}
                                                class="custom-class form-check-input" />
                                            <label class="me-2">Visible to client</label>
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <div class="col-lg-4">
                                            <x-form-components.input-label for="title" required>
                                                {{ __('tasks.Title') }}
                                            </x-form-components.input-label>
                                        </div>
                                        <div class="col-lg-8">
                                            <x-form-components.input-group type="text" name="title" id="title"
                                                placeholder="{{ __('Enter Title') }}" value="{{ old('title') }}" required
                                                class="custom-class" />
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <div class="col-lg-4">
                                            <x-form-components.input-label for="hourly_rate">
                                                {{ __('tasks.Hourly Rate') }}
                                            </x-form-components.input-label>
                                        </div>
                                        <div class="col-lg-8">
                                            <x-form-components.input-group-prepend-append type="number"
                                                class="custom-class" id="hourly_rate" step="0.001"
                                                prepend="{{ getSettingValue('Currency Symbol') }}"
                                                append="{{ getSettingValue('Currency Code') }}" name="hourly_rate"
                                                placeholder="{{ __('99999') }}" value="{{ old('hourly_rate') }}" />
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <div class="col-lg-4">
                                            <x-form-components.input-label for="start_date">
                                                {{ __('tasks.Start Date') }}
                                            </x-form-components.input-label>
                                        </div>
                                        <div class="col-lg-8">
                                            <x-form-components.input-group type="date" name="start_date" id="start_date"
                                                placeholder="{{ __('Select Date') }}" value="{{ old('start_date') }}"
                                                class="custom-class" />
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <div class="col-lg-4">
                                            <x-form-components.input-label for="due_date">
                                                {{ __('tasks.Due Date') }}
                                            </x-form-components.input-label>
                                        </div>
                                        <div class="col-lg-8">
                                            <x-form-components.input-group type="date" name="due_date" id="due_date"
                                                placeholder="{{ __('Select Date') }}" value="{{ old('due_date') }}"
                                                class="custom-class" />
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <div class="col-lg-4">
                                            <x-form-components.input-label for="priority" required>
                                                {{ __('tasks.Priority') }}
                                            </x-form-components.input-label>
                                        </div>
                                        <div class="col-lg-8">
                                            <select class="form-select" name="priority" id="priority" required>
                                                @foreach (['Low', 'Medium', 'High', 'Urgent'] as $pri)
                                                    <option value="{{ $pri }}"
                                                        {{ old('priority') == $pri ? 'selected' : '' }}>
                                                        {{ $pri }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <div class="col-lg-4">
                                            <x-form-components.input-label for="related_to" required>
                                                {{ __('tasks.Related To') }}
                                            </x-form-components.input-label>
                                        </div>
                                        <div class="col-lg-8">
                                            <select class="form-select" name="related_to" id="related_to" required>
                                                @foreach (TASKS_RELATED_TO['STATUS'] as $pri)
                                                    <option value="{{ $pri }}"
                                                        {{ old('related_to') == $pri ? 'selected' : '' }}>
                                                        {{ $pri }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>


                                    <div class="row mb-4">
                                        <div class="col-lg-4">
                                            <x-form-components.input-label for="project_id" >
                                                {{ __('tasks.Project') }}
                                            </x-form-components.input-label>
                                        </div>
                                        <div class="col-lg-8">
                                            <select class="form-select searchSelectBox" name="project_id" id="project_id" >
                                                @foreach ($projects as $pro)
                                                    <option value="{{ $pro->id }}"
                                                        {{ old('project_id') == $pro->id ? 'selected' : '' }}>
                                                        {{ $pro->title }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>




                                    <div class="row mb-4">
                                        <div class="col-lg-4">
                                            <x-form-components.input-label for="assign_to[]">
                                                {{ __('tasks.Assign To') }}
                                            </x-form-components.input-label>
                                        </div>
                                        <div class="col-lg-8">


                                            <x-form-components.dropdown-with-profile :title="'Select Team Members'" :options="$teamMates"
                                                :name="'assign_to'" :multiple="true" :selected="old('assign_to')" />


                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                      
                                            <x-form-components.input-label for="description">
                                                {{ __('tasks.Description') }}
                                            </x-form-components.input-label>
                                     
                                            <textarea name="description" id="description" class="form-control wysiwyg-editor" rows="5">{{ old('description') }}</textarea>
                                        
                                    </div>
                                    <hr>
                                    @if (isset($customFields) && $customFields->isNotEmpty())
                                        <hr>
                                        <x-form-components.tab-guidebox :nextTab="'Custom Fields'" />
                                    @endif
                                </div>

                                <!-- Custom Fields Tab -->
                                @if (isset($customFields) && $customFields->isNotEmpty())
                                    <x-form-components.custom-fields-create :customFields="$customFields" />
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection


@push('scripts')
    <script src="{{ asset('js/tinymce/tinymce.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {


            const currentTheme = document.documentElement.getAttribute('data-bs-theme');
            // Initialize WYSIWYG editor
            if (typeof tinymce !== 'undefined') {
                tinymce.init({
                    selector: '.wysiwyg-editor',
                    height: 400,
                    base_url: '/js/tinymce',
                    license_key: 'gpl',
                    skin: currentTheme === 'dark' ? 'oxide-dark' : 'oxide',
                    content_css: currentTheme === 'dark' ? 'dark' : 'default',
                    setup: function(editor) {
                        editor.on('init', function() {
                            editor.setContent(`{!! old('description', '') !!}`);
                        });
                    },
                    menubar: false,
                    plugins: [
                        'accordion',
                        'advlist',
                        'anchor',
                        'autolink',
                        'autoresize',
                        'autosave',
                        'charmap',
                        'code',
                        'codesample',
                        'directionality',
                        'emoticons',
                        'fullscreen',
                        'help',
                        'lists',
                        'link',
                        'image',


                        'preview',
                        'anchor',
                        'searchreplace',
                        'visualblocks',


                        'insertdatetime',
                        'media',
                        'table',



                        'wordcount'
                    ],
                    toolbar: 'undo redo | formatselect | bold italic backcolor | \
                                                                                                                          alignleft aligncenter alignright alignjustify | \
                                                                                                                          bullist numlist outdent indent | removeformat | help | \
                                                                                                                          link image media preview codesample table'
                });
            }

        });
    </script>
@endpush
