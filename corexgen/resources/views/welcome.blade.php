<a href="{{route(getPanelRoutes('role.index'))}}">Visit CRM </a>

<form method="POST" action="{{ route('logout') }}">
    @csrf
    <button type="submit" class="btn btn-danger">Logout</button>
</form>


<a href="/add-default-countries-cities">Add Country & Cities if queue:work is running in bg</a>