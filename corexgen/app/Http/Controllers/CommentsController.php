<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommentRequest;
use App\Models\CommentNote;
use App\Models\CRM\CRMClients;
use App\Models\CRM\CRMLeads;
use App\Models\Project;
use App\Models\Tasks;
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

    /**
     * add leads comments
     */
    public function addLeadsComment(CommentRequest $request)
    {

        try {
            //code...
            $requestData = $request->validated();
            $query = CRMLeads::where('id', $requestData['id']);
            $query = $this->applyTenantFilter($query);
            $lead = $query->firstOrFail();

            $comment = $this->commentService->add($lead, $requestData);


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


            if ($request->ajax()) {
                return response()->json([
                    'success' => 'Comment note added.',
                    'comment' => $comment,
                    'user' => Auth::user()
                ]);
            }


            return redirect()
                ->back()
                ->with('success', 'New comment  / note added.');
        } catch (\Exception $e) {

            if ($request->ajax()) {
                return response()->json(['error' => 'An error occurred while adding the comment / notes.' . $e->getMessage()]);
            }
            //throw $th;
            return redirect()
                ->back()
                ->with('error', 'An error occurred while adding the comment / notes. ' . $e->getMessage());
        }

    }

    /**
     * destroy leads comments
     */
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

    /**
     * add clients comments
     */
    public function addClientsComment(CommentRequest $request)
    {

        try {
            //code...
            $requestData = $request->validated();
            $query = CRMClients::where('id', $requestData['id']);
            $query = $this->applyTenantFilter($query);
            $client = $query->firstOrFail();

            $comment = $this->commentService->add($client, $requestData);


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

            if ($request->ajax()) {
                return response()->json([
                    'success' => 'Comment note added.',
                    'comment' => $comment,
                    'user' => Auth::user()
                ]);
            }

            return redirect()
                ->back()
                ->with('success', 'New comment  / note added.');
        } catch (\Exception $e) {

            if ($request->ajax()) {
                return response()->json(['error' => 'An error occurred while adding the comment / notes.' . $e->getMessage()]);
            }
            //throw $th;
            return redirect()
                ->back()
                ->with('error', 'An error occurred while adding the comment / notes. ' . $e->getMessage());
        }

    }

    /**
     * destroy clients comments
     */
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
    /**
     * add projects comments
     */
    public function addProjectsComment(CommentRequest $request)
    {

        try {
            //code...
            $requestData = $request->validated();
            $query = Project::where('id', $requestData['id']);
            $query = $this->applyTenantFilter($query);
            $project = $query->firstOrFail();

            $comment = $this->commentService->add($project, $requestData);


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

            if ($request->ajax()) {
                return response()->json([
                    'success' => 'Comment note added.',
                    'comment' => $comment,
                    'user' => Auth::user()
                ]);
            }
            return redirect()
                ->back()
                ->with('success', 'New comment  / note added.');
        } catch (\Exception $e) {
            //throw $th;

            if ($request->ajax()) {
                return response()->json(['error' => 'An error occurred while adding the comment / notes.' . $e->getMessage()]);
            }

            return redirect()
                ->back()
                ->with('error', 'An error occurred while adding the comment / notes. ' . $e->getMessage());
        }

    }

    /**
     * destory projects comments
     */
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

    // tasks
    /**
     * add tasks comments
     */
    public function addTasksComment(CommentRequest $request)
    {

        try {
            //code...
            $requestData = $request->validated();
            $query = Tasks::where('id', $requestData['id']);
            $query = $this->applyTenantFilter($query);
            $project = $query->firstOrFail();

            $comment = $this->commentService->add($project, $requestData);


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

            if ($request->ajax()) {
                return response()->json([
                    'success' => 'Comment note added.',
                    'comment' => $comment,
                    'user' => Auth::user()
                ]);
            }

            return redirect()
                ->back()
                ->with('success', 'New comment  / note added.');
        } catch (\Exception $e) {

            if ($request->ajax()) {
                return response()->json(['error' => 'An error occurred while adding the comment / notes.' . $e->getMessage()]);
            }
            //throw $th;
            return redirect()
                ->back()
                ->with('error', 'An error occurred while adding the comment / notes. ' . $e->getMessage());
        }

    }

    /**
     * destory tasks comments
     */
    public function destroyTasksComment($id)
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
