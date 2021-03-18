@if (Session::has('info'))
    <div class="alert alert-secondary" role="alert">
        {{Session::get('info')}}
    </div>

@endif
