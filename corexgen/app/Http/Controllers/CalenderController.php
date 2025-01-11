<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\CalendarRequest;
use App\Models\Calender;
use App\Traits\StatusStatsFilter;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use App\Traits\TenantFilter;
use App\Helpers\PermissionsHelper;
use App\Traits\SubscriptionUsageFilter;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CalenderController extends Controller
{
    use TenantFilter;
    use SubscriptionUsageFilter;
    use StatusStatsFilter;

    private $viewDir = 'dashboard.calender.';

    private function getViewFilePath($filename)
    {
        return $this->viewDir . $filename;
    }

    /**
     * Display calendar view
     */
    public function index(Request $request)
    {

        return view($this->getViewFilePath('index'), [
            'title' => 'Calendar',
            'permissions' => PermissionsHelper::getPermissionsArray('CALENDER'),
            'module' => PANEL_MODULES[$this->getPanelModule()]['calender']
        ]);
    }

    public function fetch(Request $request)
    {
        $start = $request->input('start');
        $end = $request->input('end');

        $query = Calender::query()
            ->whereBetween('start_date', [$start, $end])
            ->orWhereBetween('end_date', [$start, $end])
            ->orWhere(function ($query) use ($start, $end) {
                $query->where('start_date', '<=', $start)
                    ->where('end_date', '>=', $end);
            });

        $query = $this->applyTenantFilter($query);

        $events = $query->get()->map(function ($event) {
            return [
                'id' => $event->id,
                'title' => $event->title,
                'start' => $event->start_date,
                'end' => $event->end_date,
                'description' => $event->description,
                'location' => $event->location,
                'className' => $this->getEventClassName($event),
                'extendedProps' => [
                    'event_type' => $event->event_type,
                    'priority' => $event->priority,
                    'attendees' => $event->attendees,
                    'is_recurring' => $event->is_recurring,
                    'status' => $event->status
                ]
            ];
        });

        return response()->json($events);
    }
    /**
     * Show event creation form
     */
    public function create(Request $request)
    {
        $this->checkCurrentUsage(strtolower(PLANS_FEATURES['CALENDER']));

        $defaultDate = $request->input('date') ?? now()->format('Y-m-d H:i:s');

        return view($this->getViewFilePath('create'), [
            'title' => 'Create Event',
            'defaultDate' => $defaultDate
        ]);
    }

    /**
     * Store new event
     */
    public function store(CalendarRequest $request)
    {
        try {
            // Start a transaction to ensure atomicity
            DB::beginTransaction();

            $validated = $request->validated();
            $validated['company_id'] = Auth::user()->company_id;
            $validated['created_by'] = Auth::id();

            // Create the main event
            $event = Calender::create($validated);

            // Handle recurring events
            // if ($event->is_recurring) {
            //     $recurringEvents = $event->generateRecurringEvents();
            //     foreach ($recurringEvents as $recurringEvent) {
            //         $recurringEvent->save(); // Save each recurring event
            //     }
            // }

            // Update usage metrics
            $this->updateUsage(
                strtolower(PLANS_FEATURES[PermissionsHelper::$plansPermissionsKeys['CALENDER']]),
                '+',
                '1'
            );

            DB::commit();

            // Return success response

            return redirect()
                ->route($this->getTenantRoute() . 'calender.index')
                ->with('success', 'Event created successfully.');
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback on error

            // Log the error for debugging
            Log::error('Error creating calendar event', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()
                ->back()
                ->with('error', 'An error occurred while creating the event. ' . $e->getMessage());
        }
    }


    /**
     * Update event
     */
    public function update(CalendarRequest $request)
    {
        try {
            $validated = $request->validated();

            $query = Calender::query()->where('id', $request->id);
            $query = $this->applyTenantFilter($query);
            $event = $query->firstOrFail();

            $event->update($validated);

            // if ($event->is_recurring && $event->wasChanged(['recurrence_pattern', 'recurrence_interval'])) {
            //     Calender::where('parent_id', $event->id)->delete();
            //     $recurringEvents = $event->generateRecurringEvents();
            //     foreach ($recurringEvents as $recurringEvent) {
            //         $recurringEvent->save();
            //     }
            // }

            return response()->json([
                'success' => true,
                'message' => 'Event updated successfully',
                'event' => $this->formatEventForCalendar($event)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating event: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * View event details
     */
    public function view($id)
    {
        $query = Calender::query()->where('id', $id);
        $query = $this->applyTenantFilter($query);
        $event = $query->with(['creator', 'company'])->firstOrFail();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'event' => $event
            ]);
        }

        return view($this->getViewFilePath('view'), [
            'title' => 'View Event',
            'event' => $event
        ]);
    }

    public function edit($id)
    {
        $query = Calender::query()->where('id', $id);
        $query = $this->applyTenantFilter($query);
        $event = $query->with(['creator', 'company'])->firstOrFail();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'event' => $event
            ]);
        }

        return view($this->getViewFilePath('edit'), [
            'title' => 'Edit Event',
            'event' => $event
        ]);
    }

    /**
     * Delete event
     */
    public function destroy($id)
    {
        try {
            $query = Calender::query()->where('id', $id);
            $query = $this->applyTenantFilter($query);
            $query->delete();

            $this->updateUsage(
                strtolower(PLANS_FEATURES[PermissionsHelper::$plansPermissionsKeys['CALENDER']]),
                '-',
                '1'
            );

            return response()->json([
                'success' => true,
                'message' => 'Event deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting event: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get event class name based on status/priority
     */
    private function getEventClassName($event)
    {
        $classes = [];

        // Add status class
        $classes[] = 'event-status-' . $event->status;

        // Add priority class
        if ($event->priority) {
            $classes[] = 'event-priority-' . $event->priority;
        }

        // Add recurring class
        if ($event->is_recurring) {
            $classes[] = 'event-recurring';
        }

        return implode(' ', $classes);
    }

    /**
     * Format event for calendar display
     */
    private function formatEventForCalendar($event)
    {
        return [
            'id' => $event->id,
            'title' => $event->title,
            'start' => $event->start_date,
            'end' => $event->end_date,
            'description' => $event->description,
            'location' => $event->location,
            'className' => $this->getEventClassName($event),
            'extendedProps' => [
                'event_type' => $event->event_type,
                'priority' => $event->priority,
                'attendees' => $event->attendees,
                'is_recurring' => $event->is_recurring,
                'status' => $event->status
            ]
        ];
    }
}