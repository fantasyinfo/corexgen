@extends('emails.layout')

@section('title', 'New Comment Added')

@section('content')
    <div style="margin-bottom: 30px;">
        <p style="margin-bottom: 20px; font-size: 16px; color: #4a5568;">
            A new comment has been added to the <strong
                style="color: #2d3748;">{{ $modal }}</strong>
        </p>

        <p style="margin-bottom: 20px; font-size: 14px; color: #1b2027;">{!! $comment !!} </p>

        <div style="margin-bottom: 25px;">
            <p style="margin: 0; color: #718096; font-size: 15px;">Commented By:
                <strong style="color: #2d3748;">{{ $commentedBy->name }}</strong>
            </p>
        </div>

        <div style="text-align: center; margin-top: 30px;">
            <a href="{{ route(getPanelRoutes($view), ['id' => $id]) }}"
                style="display: inline-block; background-color: #4CAF50; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold; text-transform: uppercase; font-size: 14px; letter-spacing: 0.5px; transition: background-color 0.3s ease;">
                View {{$modal}} Details
            </a>
        </div>
    </div>
@endsection
