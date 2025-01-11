@extends('layout.app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="justify-content-md-center col-lg-9">
                <div class="card stretch stretch-full">
                    <form id="calenderEventForm" action="{{ route(getPanelRoutes('calender.update')) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="id" value="{{ $event->id }}">
                        <div class="card-body general-info">
                            <div class="mb-5 d-flex align-items-center justify-content-between">
                                <p class="fw-bold mb-0 me-4">
                                    <span class="d-block">{{ __('Update Event') }}</span>
                                    <span class="fs-12 fw-normal text-muted text-truncate-1-line">
                                        {{ __('crud.Please add correct information') }}
                                    </span>
                                </p>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> <span>{{ __('Update Event') }}</span>
                                </button>
                            </div>

                            {{-- Title --}}
                            <div class="row mb-4 align-items-center">
                                <div class="col-lg-4">
                                    <label for="title" class="form-label">{{ __('Title') }} <span
                                            class="text-danger">*</span></label>
                                </div>
                                <div class="col-lg-8">
                                    <input type="text" class="form-control" name="title" id="title"
                                        value="{{ old('title', $event->title) }}" required placeholder="Enter event title">
                                </div>
                            </div>

                            {{-- Description --}}
                            <div class="row mb-4 align-items-center">
                                <div class="col-lg-4">
                                    <label for="description" class="form-label">{{ __('Description') }}</label>
                                </div>
                                <div class="col-lg-8">
                                    <textarea class="form-control" name="description" id="description" rows="3" placeholder="Enter description">{{ old('description', $event->description) }}</textarea>
                                </div>
                            </div>

                            {{-- Event Type --}}
                            <div class="row mb-4 align-items-center">
                                <div class="col-lg-4">
                                    <label for="event_type" class="form-label">{{ __('Event Type') }}</label>
                                </div>
                                <div class="col-lg-8">
                                    <select name="event_type" id="event_type" class="form-select">
                                        <option value="meeting" {{ $event->event_type == 'meeting' ? 'selected' : '' }}>
                                            {{ __('Meeting') }}</option>
                                        <option value="task" {{ $event->event_type == 'task' ? 'selected' : '' }}>
                                            {{ __('Task') }}</option>
                                        <option value="appointment"
                                            {{ $event->event_type == 'appointment' ? 'selected' : '' }}>
                                            {{ __('Appointment') }}</option>
                                    </select>
                                </div>
                            </div>

                            {{-- Priority --}}
                            <div class="row mb-4 align-items-center">
                                <div class="col-lg-4">
                                    <label for="priority" class="form-label">{{ __('Priority') }}</label>
                                </div>
                                <div class="col-lg-8">
                                    <select name="priority" id="priority" class="form-select">
                                        <option value="high" {{ $event->priority == 'high' ? 'selected' : '' }}>
                                            {{ __('High') }}</option>
                                        <option value="medium" {{ $event->priority == 'medium' ? 'selected' : '' }}>
                                            {{ __('Medium') }}</option>
                                        <option value="low" {{ $event->priority == 'low' ? 'selected' : '' }}>
                                            {{ __('Low') }}</option>
                                    </select>
                                </div>
                            </div>

                            {{-- Start Date --}}
                            <div class="row mb-4 align-items-center">
                                <div class="col-lg-4">
                                    <label for="start_date" class="form-label">{{ __('Start Date') }} <span
                                            class="text-danger">*</span></label>
                                </div>
                                <div class="col-lg-8">
                                    <input type="datetime-local" class="form-control" name="start_date" id="start_date"
                                        value="{{ old('start_date', $event->start_date) }}" required>
                                </div>
                            </div>

                            {{-- End Date --}}
                            <div class="row mb-4 align-items-center">
                                <div class="col-lg-4">
                                    <label for="end_date" class="form-label">{{ __('End Date') }}</label>
                                </div>
                                <div class="col-lg-8">
                                    <input type="datetime-local" class="form-control" name="end_date" id="end_date"
                                        value="{{ old('end_date', $event->end_date) }}">
                                </div>
                            </div>

                            {{-- Location --}}
                            <div class="row mb-4 align-items-center">
                                <div class="col-lg-4">
                                    <label for="location" class="form-label">{{ __('Location') }}</label>
                                </div>
                                <div class="col-lg-8">
                                    <input type="text" class="form-control" name="location" id="location"
                                        value="{{ old('location', $event->location) }}" placeholder="Enter location">
                                </div>
                            </div>

                            <div class="row mb-4 align-items-center">
                                <div class="col-lg-4">
                                    <label for="color" class="form-label">{{ __('Color') }}</label>
                                </div>
                                <div class="col-lg-8">
                                    <input type="color" class="form-control" name="color" id="color"
                                        value="{{ old('color', $event->color) }}" placeholder="Enter Color">
                                </div>
                            </div>

                            {{-- Meeting Link --}}
                            <div class="row mb-4 align-items-center">
                                <div class="col-lg-4">
                                    <label for="meeting_link" class="form-label">{{ __('Meeting Link') }}</label>
                                </div>
                                <div class="col-lg-8">
                                    <input type="url" class="form-control" name="meeting_link" id="meeting_link"
                                        value="{{ old('meeting_link', $event->meeting_link) }}"
                                        placeholder="Enter meeting link">
                                </div>
                            </div>

                            {{-- Timezone --}}
                            <div class="row mb-4 align-items-center">
                                <div class="col-lg-4">
                                    <label for="timezone" class="form-label">{{ __('Timezone') }}</label>
                                </div>
                                <div class="col-lg-8">
                                    <select name="timezone" id="timezone" class="form-select">
                                        @foreach (timezone_identifiers_list() as $timezone)
                                            <option value="{{ $timezone }}"
                                                {{ $event->timezone == $timezone ? 'selected' : '' }}>{{ $timezone }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>



                            {{-- Status --}}
                            <div class="row mb-4 align-items-center">
                                <div class="col-lg-4">
                                    <label for="status" class="form-label">{{ __('Status') }}</label>
                                </div>
                                <div class="col-lg-8">
                                    <select name="status" id="status" class="form-select">
                                        <option value="upcoming" {{ $event->status == 'upcoming' ? 'selected' : '' }}>
                                            {{ __('Upcoming') }}</option>
                                        <option value="in_progress"
                                            {{ $event->status == 'in_progress' ? 'selected' : '' }}>
                                            {{ __('In Progress') }}</option>
                                        <option value="completed" {{ $event->status == 'completed' ? 'selected' : '' }}>
                                            {{ __('Completed') }}</option>
                                        <option value="canceled" {{ $event->status == 'canceled' ? 'selected' : '' }}>
                                            {{ __('Canceled') }}</option>
                                        <option value="postponed" {{ $event->status == 'postponed' ? 'selected' : '' }}>
                                            {{ __('Postponed') }}</option>
                                    </select>
                                </div>
                            </div>

                            {{-- Send Notifications --}}
                            <div class="row mb-4 align-items-center">
                                <div class="col-lg-4">
                                    <label for="send_notifications"
                                        class="form-label">{{ __('Send Notifications') }}</label>
                                </div>
                                <div class="col-lg-8">
                                    <input type="checkbox" name="send_notifications" id="send_notifications"
                                        value="1"
                                        {{ old('send_notifications', $event->send_notifications) ? 'checked' : '' }}>
                                </div>
                            </div>

                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
