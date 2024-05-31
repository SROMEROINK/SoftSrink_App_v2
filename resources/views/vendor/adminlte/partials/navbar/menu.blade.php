{{-- resources/views/vendor/adminlte/partials/navbar/menu.blade.php --}}

<ul class="navbar-nav ml-auto">
    @if (Auth::check())
        <li class="nav-item dropdown user-menu">
            <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
                <img src="{{ Auth::user()->adminlte_image() }}" class="user-image img-circle elevation-2" alt="User Image">
                <span class="d-none d-md-inline">{{ Auth::user()->name }}</span>
            </a>
            <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                <!-- User image -->
                <li class="user-header bg-primary">
                    <img src="{{ Auth::user()->adminlte_image() }}" class="img-circle elevation-2" alt="User Image">
                    <p>
                        {{ Auth::user()->name }}
                        <small>{{ Auth::user()->adminlte_desc() }}</small>
                    </p>
                </li>
                <!-- Menu Body -->
                <!-- Aquí podrías agregar más elementos si es necesario -->
                <!-- Menu Footer-->
                <li class="user-footer">
                    <a href="{{ route('profile.show') }}" class="btn btn-default btn-flat">Profile</a>
                    <a href="#" class="btn btn-default btn-flat float-right"
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Sign out</a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </li>
            </ul>
        </li>
    @endif
</ul>
