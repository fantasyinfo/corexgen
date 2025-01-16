@props(['link' => 'users.create', 'text' => 'Create new'])
<a class="my-1 link   " href="{{ route(getPanelRoutes($link)) }}"
    target="_blank">{{ $text }}</a>
