@extends('layout.app')

@section('content')
    @php

        $type = null;
        $id = null;
        $refrer = null;
        if (isset($_GET['type']) && isset($_GET['id']) && isset($_GET['refrer'])) {
            $type = trim($_GET['type']);
            $id = trim($_GET['id']);
            $refrer = trim($_GET['refrer']);
        }

        // prePrintR($tax->toArray());

    @endphp

    <div class="container-fluid">
        <div class="row">
            <div class="justify-content-md-center col-lg-12">
                <div class="card stretch stretch-full">
                    <form id="invoiceFieldsForm" action="{{ route(getPanelRoutes('invoices.store')) }}" method="POST">
                        @csrf
                        @if ($id && $id > 0)
                            <input type="hidden" name="project_id" value="{{ $id }}" />
                        @endif
                        <div class="card-body general-info">
                            <div class="mb-5 d-flex align-items-center justify-content-between">
                                <p class="fw-bold mb-0 me-4">
                                    <span class="d-block">{{ __('invoices.Create New Invoice') }}</span>
                                    <span class="fs-12 fw-normal text-muted text-truncate-1-line">
                                        {{ __('crud.Please add correct information') }}
                                    </span>
                                </p>
                                <div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> {{ __('invoices.Create Invoice') }}
                                    </button>
                                </div>
                            </div>

                            <div class="row mb-4 align-items-center">
                                <div class="col-lg-4">
                                    <x-form-components.input-label for="_id" class="custom-class" required>
                                        {{ __('invoices.ID') }}
                                    </x-form-components.input-label>
                                </div>
                                <div class="col-lg-8">
                                    <x-form-components.create-new :link="'settings.oneWord'" :text="'Change Prefix'" />
                                    <x-form-components.input-group-prepend-append
                                        prepend="{{ getSettingValue('Invoice Prefix') }}-" append="..." type="text"
                                        class="custom-class" id="_id" name="_id" placeholder="{{ __('0001') }}"
                                        value="{{ old('_id', $lastId) }}" required />
                                </div>
                                <p class="offset-lg-4 font-12 my-2 text-secondary">
                                    <span class="text-success"> Auto-Increment (Last ID + 1)</span> by default. Can be
                                    modify also.
                                </p>
                            </div>


                            <div class="row mb-3 align-items-center">
                                <div class="col-lg-4">
                                    <x-form-components.input-label for="client_id" required>
                                        {{ __('invoices.Select Client') }}
                                    </x-form-components.input-label>
                                </div>
                                <div class="col-lg-8">
                                    <x-form-components.create-new :link="'clients.create'" :text="'Create new'" />
                                    <select name="client_id" id="client_id" class="form-select searchSelectBox">
                                        @foreach ($clients as $item)
                                            @php
                                                $nameAndEmail = $item->first_name . ' ' . $item->last_name;
                                                if ($item->type == 'Company') {
                                                    $nameAndEmail = $item->company_name;
                                                }
                                                $nameAndEmail .= !$item->primary_email
                                                    ? ' [No Email Found...] '
                                                    : " [ $item->primary_email ]";
                                            @endphp
                                            <option value="{{ $item->id }}"
                                                {{ old('client_id') == $item->id ? 'selected' : '' }}>{{ $nameAndEmail }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3 align-items-center">
                                <div class="col-lg-4">
                                    <x-form-components.input-label for="task_id" required>
                                        {{ __('invoices.Select Task') }}
                                    </x-form-components.input-label>
                                </div>
                                <div class="col-lg-8">
                                    <x-form-components.create-new :link="'tasks.create'" :text="'Create new'" />
                                    <select name="task_id" id="task_id" class="form-select searchSelectBox">
                                        @foreach ($tasks as $item)
                                            <option value="{{ $item->id }}"
                                                {{ old('task_id') == $item->id ? 'selected' : '' }}>{{ $item->title }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>


                            <div class="row mb-4 align-items-center">
                                <div class="col-lg-4">

                                    <x-form-components.input-label for="issue_date" class="custom-class" required>
                                        {{ __('invoices.Issue Date') }}
                                    </x-form-components.input-label>
                                </div>
                                <div class="col-lg-8">
                                    <x-form-components.input-group type="date" placeholder="Select Date"
                                        class="custom-class" id="issue_date" name="issue_date"
                                        value="{{ old('issue_date') }}" required />

                                </div>
                            </div>
                            <div class="row mb-4 align-items-center">
                                <div class="col-lg-4">

                                    <x-form-components.input-label for="due_date" class="custom-class">
                                        {{ __('invoices.Due Date') }}
                                    </x-form-components.input-label>
                                </div>
                                <div class="col-lg-8">
                                    <x-form-components.input-group type="date" placeholder="Select Date"
                                        class="custom-class" id="due_date" name="due_date"
                                        value="{{ old('due_date') }}" />
                                </div>
                            </div>







                            <div class="row mb-4 align-items-center">

                                <div class="col-lg-4">
                                    <x-form-components.input-label for="valid_date" class="custom-class">
                                        {{ __('invoices.Details') }}
                                    </x-form-components.input-label>
                                </div>
                                <div class="col-lg-8">
                                    <x-form-components.textarea-group name="notes" id="notes"
                                        placeholder="Extra details, conditions, rules, commitments, products, services, discouts, tax ... if any"
                                        value="{{ old('notes') }}" class="custom-class notes" />
                                </div>
                            </div>
                            @include('dashboard.crm.invoices.components._itemCreate')
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
