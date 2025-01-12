<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttachmentRequest;
use App\Models\Attachments;
use App\Models\CRM\CRMClients;
use App\Models\CRM\CRMLeads;
use App\Models\Project;
use App\Models\Tasks;
use App\Services\AttachmentService;
use App\Traits\TenantFilter;
use Illuminate\Http\Request;

class AttachmentController extends Controller
{
    use TenantFilter;

    protected $attachmentService;

    public function __construct(AttachmentService $attachmentService)
    {
        $this->attachmentService = $attachmentService;
    }

    /**
     * add leads attachments
     */
    public function addLeadsAttachment(AttachmentRequest $request)
    {

        try {
            //code...
            $requestData = $request->validated();
            $query = CRMLeads::where('id', $requestData['id']);
            $query = $this->applyTenantFilter($query);
            $lead = $query->firstOrFail();

            $this->attachmentService->add($lead, $requestData);


            // Log the detach operation as an audit
            $lead->audits()->create([
                'old_values' => [],
                'new_values' => ['attachment' => 'New Attachment...'],
                'user_type' => 'App\Models\User',
                'event' => 'created',
                'url' => request()->fullUrl(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->header('User-Agent'),
            ]);

            return redirect()
                ->back()
                ->with('success', 'New cattachment added.');
        } catch (\Exception $e) {
            //throw $th;
            return redirect()
                ->back()
                ->with('error', 'An error occurred while adding the attachment ' . $e->getMessage());
        }
    }

    /**
     * remove leads attachments
     */
    public function destroyLeadsAttachment($id, AttachmentService $attachmentService)
    {
        try {
            // Retrieve the attachment
            $attachment = $this->applyTenantFilter(Attachments::where('id', $id))->first();

            if (!$attachment) {
                return redirect()
                    ->back()
                    ->with('error', 'Attachment not found.');
            }

            // Use the AttachmentService to delete the media
            if ($attachmentService->deleteMedia($attachment)) {
                return redirect()
                    ->back()
                    ->with('success', 'Attachment deleted successfully.');
            }

            return redirect()
                ->back()
                ->with('error', 'An error occurred while deleting the attachment.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    /**
     * add clients attachments
     */
    public function addClientsAttachment(AttachmentRequest $request)
    {

        try {
            //code...
            $requestData = $request->validated();
            $query = CRMClients::where('id', $requestData['id']);
            $query = $this->applyTenantFilter($query);
            $lead = $query->firstOrFail();

            $this->attachmentService->add($lead, $requestData);


            // Log the detach operation as an audit
            $lead->audits()->create([
                'old_values' => [],
                'new_values' => ['attachment' => 'New Attachment...'],
                'user_type' => 'App\Models\User',
                'event' => 'created',
                'url' => request()->fullUrl(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->header('User-Agent'),
            ]);

            return redirect()
                ->back()
                ->with('success', 'New cattachment added.');
        } catch (\Exception $e) {
            //throw $th;
            return redirect()
                ->back()
                ->with('error', 'An error occurred while adding the attachment ' . $e->getMessage());
        }
    }

    /**
     * remove clients attachments
     */
    public function destroyClientsAttachment($id, AttachmentService $attachmentService)
    {
        try {
            // Retrieve the attachment
            $attachment = $this->applyTenantFilter(Attachments::where('id', $id))->first();

            if (!$attachment) {
                return redirect()
                    ->back()
                    ->with('error', 'Attachment not found.');
            }

            // Use the AttachmentService to delete the media
            if ($attachmentService->deleteMedia($attachment)) {
                return redirect()
                    ->back()
                    ->with('success', 'Attachment deleted successfully.');
            }

            return redirect()
                ->back()
                ->with('error', 'An error occurred while deleting the attachment.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }


    // projects
    /**
     * add projects attachments
     */
    public function addProjectsAttachment(AttachmentRequest $request)
    {

        try {
            //code...
            $requestData = $request->validated();
            $query = Project::where('id', $requestData['id']);
            $query = $this->applyTenantFilter($query);
            $lead = $query->firstOrFail();

            $this->attachmentService->add($lead, $requestData);


            // Log the detach operation as an audit
            $lead->audits()->create([
                'old_values' => [],
                'new_values' => ['attachment' => 'New Attachment...'],
                'user_type' => 'App\Models\User',
                'event' => 'created',
                'url' => request()->fullUrl(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->header('User-Agent'),
            ]);

            return redirect()
                ->back()
                ->with('success', 'New cattachment added.');
        } catch (\Exception $e) {
            //throw $th;
            return redirect()
                ->back()
                ->with('error', 'An error occurred while adding the attachment ' . $e->getMessage());
        }
    }

    /**
     * remove projects attachments
     */
    public function destroyProjectsAttachment($id, AttachmentService $attachmentService)
    {
        try {
            // Retrieve the attachment
            $attachment = $this->applyTenantFilter(Attachments::where('id', $id))->first();

            if (!$attachment) {
                return redirect()
                    ->back()
                    ->with('error', 'Attachment not found.');
            }

            // Use the AttachmentService to delete the media
            if ($attachmentService->deleteMedia($attachment)) {
                return redirect()
                    ->back()
                    ->with('success', 'Attachment deleted successfully.');
            }

            return redirect()
                ->back()
                ->with('error', 'An error occurred while deleting the attachment.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    // tasks

    /**
     * add tasks attachments
     */
    public function addTasksAttachment(AttachmentRequest $request)
    {

        try {
            //code...
            $requestData = $request->validated();
            $query = Tasks::where('id', $requestData['id']);
            $query = $this->applyTenantFilter($query);
            $lead = $query->firstOrFail();

            $this->attachmentService->add($lead, $requestData);


            // Log the detach operation as an audit
            $lead->audits()->create([
                'old_values' => [],
                'new_values' => ['attachment' => 'New Attachment...'],
                'user_type' => 'App\Models\User',
                'event' => 'created',
                'url' => request()->fullUrl(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->header('User-Agent'),
            ]);

            return redirect()
                ->back()
                ->with('success', 'New cattachment added.');
        } catch (\Exception $e) {
            //throw $th;
            return redirect()
                ->back()
                ->with('error', 'An error occurred while adding the attachment ' . $e->getMessage());
        }
    }

    /**
     * remove tasks atachments
     */
    public function destroyTasksAttachment($id, AttachmentService $attachmentService)
    {
        try {
            // Retrieve the attachment
            $attachment = $this->applyTenantFilter(Attachments::where('id', $id))->first();

            if (!$attachment) {
                return redirect()
                    ->back()
                    ->with('error', 'Attachment not found.');
            }

            // Use the AttachmentService to delete the media
            if ($attachmentService->deleteMedia($attachment)) {
                return redirect()
                    ->back()
                    ->with('success', 'Attachment deleted successfully.');
            }

            return redirect()
                ->back()
                ->with('error', 'An error occurred while deleting the attachment.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }
}
