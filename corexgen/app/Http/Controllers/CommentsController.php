<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommentRequest;
use App\Models\CommentNote;
use App\Models\CRM\CRMClients;
use App\Models\CRM\CRMLeads;
use App\Models\Project;
use App\Services\CommentService;
use App\Traits\TenantFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentsController extends Controller
{
    //
    use TenantFilter;

    protected $commentService;

    public function __construct(CommentService $commentService)
    {
        $this->commentService = $commentService;
    }
    public function addLeadsComment(CommentRequest $request)
    {

        try {
            //code...
            $requestData = $request->validated();
            $query = CRMLeads::where('id', $requestData['id']);
            $query = $this->applyTenantFilter($query);
            $lead = $query->firstOrFail();

            $this->commentService->add($lead, $requestData);


            // Log the detach operation as an audit
            $lead->audits()->create([
                'old_values' => [],
                'new_values' => ['comment' => $requestData['comment']],
                'user_type' => 'App\Models\User',
                'event' => 'created',
                'url' => request()->fullUrl(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->header('User-Agent'),
            ]);

            return redirect()
                ->back()
                ->with('success', 'New comment  / note added.');
        } catch (\Exception $e) {
            //throw $th;
            return redirect()
                ->back()
                ->with('error', 'An error occurred while adding the comment / notes. ' . $e->getMessage());
        }

    }

    public function destroyLeadsComment($id)
    {
        try {
            $this->applyTenantFilter(CommentNote::where('id', $id))->delete();
            //code...
            return redirect()
                ->back()
                ->with('success', 'comment  / note deleted.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'An error occurred while deleting the comment / notes. ' . $e->getMessage());
        }

    }

    public function addClientsComment(CommentRequest $request)
    {

        try {
            //code...
            $requestData = $request->validated();
            $query = CRMClients::where('id', $requestData['id']);
            $query = $this->applyTenantFilter($query);
            $client = $query->firstOrFail();

            $this->commentService->add($client, $requestData);


            // Log the detach operation as an audit
            $client->audits()->create([
                'old_values' => [],
                'new_values' => ['comment' => $requestData['comment']],
                'user_type' => 'App\Models\User',
                'event' => 'created',
                'url' => request()->fullUrl(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->header('User-Agent'),
            ]);

            return redirect()
                ->back()
                ->with('success', 'New comment  / note added.');
        } catch (\Exception $e) {
            //throw $th;
            return redirect()
                ->back()
                ->with('error', 'An error occurred while adding the comment / notes. ' . $e->getMessage());
        }

    }

    public function destroyClientsComment($id)
    {
        try {
            $this->applyTenantFilter(CommentNote::where('id', $id))->delete();
            //code...
            return redirect()
                ->back()
                ->with('success', 'comment  / note deleted.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'An error occurred while deleting the comment / notes. ' . $e->getMessage());
        }

    }

    // projects
    public function addProjectsComment(CommentRequest $request)
    {

        try {
            //code...
            $requestData = $request->validated();
            $query = Project::where('id', $requestData['id']);
            $query = $this->applyTenantFilter($query);
            $project = $query->firstOrFail();

            $this->commentService->add($project, $requestData);


            // Log the detach operation as an audit
            $project->audits()->create([
                'old_values' => [],
                'new_values' => ['comment' => $requestData['comment']],
                'user_type' => 'App\Models\User',
                'event' => 'created',
                'url' => request()->fullUrl(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->header('User-Agent'),
            ]);

            return redirect()
                ->back()
                ->with('success', 'New comment  / note added.');
        } catch (\Exception $e) {
            //throw $th;
            return redirect()
                ->back()
                ->with('error', 'An error occurred while adding the comment / notes. ' . $e->getMessage());
        }

    }

    public function destroyProjectsComment($id)
    {
        try {
            $this->applyTenantFilter(CommentNote::where('id', $id))->delete();
            //code...
            return redirect()
                ->back()
                ->with('success', 'comment  / note deleted.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'An error occurred while deleting the comment / notes. ' . $e->getMessage());
        }

    }
}
