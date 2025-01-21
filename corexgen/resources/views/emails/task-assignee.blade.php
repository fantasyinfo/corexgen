@extends('emails.layout')

@section('title', 'Task Assignee')

@section('content')
    <div style="margin-bottom: 30px;">
        <p style="margin-bottom: 20px; font-size: 16px; color: #4a5568;">
            A new task <strong style="color: #2d3748;">{{ $task->title }}</strong> has been assignee to You (<strong
                style="color: #2d3748;">{{ $assigneeTo->name }}</strong>)
        </p>

        <div style="margin-bottom: 25px;">
            <p style="margin: 0; color: #718096; font-size: 15px;">Assigned By:
                <strong style="color: #2d3748;">{{ $assigneeBy->name }}</strong>
            </p>
        </div>

        <div style="text-align: center; margin-top: 30px;">
            <a href="{{ route(getPanelRoutes('tasks.view'), ['id' => $task->id]) }}"
                style="display: inline-block; background-color: #4CAF50; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold; text-transform: uppercase; font-size: 14px; letter-spacing: 0.5px; transition: background-color 0.3s ease;">
                View Task Details
            </a>
        </div>
    </div>
@endsection
