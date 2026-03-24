@inject('layoutHelper', 'JeroenNoten\LaravelAdminLte\Helpers\LayoutHelper')

<nav class="main-header navbar
    {{ config('adminlte.classes_topnav_nav', 'navbar-expand') }}
    {{ config('adminlte.classes_topnav', 'navbar-white navbar-light') }}">

    {{-- Navbar left links --}}
    <ul class="navbar-nav">
        {{-- Left sidebar toggler link --}}
        @include('adminlte::partials.navbar.menu-item-left-sidebar-toggler')

        {{-- Configured left links --}}
        @each('adminlte::partials.navbar.menu-item', $adminlte->menu('navbar-left'), 'item')

        {{-- Custom left links --}}
        @yield('content_top_nav_left')
    </ul>

    {{-- Navbar right links --}}
    <ul class="navbar-nav ml-auto flex items-center">
        {{-- Custom right links --}}
        @yield('content_top_nav_right')

        {{-- Configured right links --}}
        @each('adminlte::partials.navbar.menu-item', $adminlte->menu('navbar-right'), 'item')

        {{-- INTEGRATED SIGN OUT BUTTON (Replacing Name) --}}
        @if(Auth::user())
            <li class="nav-item">
                <a class="nav-link flex items-center gap-2 group transition-all cursor-pointer px-3" 
                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    
                    {{-- Exit Icon --}}
                    <i class="fas fa-sign-out-alt text-slate-400 group-hover:text-red-500 transition-colors"></i>
                    
                    {{-- Text replacement for "Joshua" --}}
                    <span class="font-bold text-[11px] uppercase tracking-[0.15em] text-slate-600 group-hover:text-red-600 transition-colors">
                        Sign Out
                    </span>
                </a>

                {{-- Hidden Form for Laravel Security --}}
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                    @csrf
                </form>
            </li>
        @endif

        {{-- Right sidebar toggler link --}}
        @if($layoutHelper->isRightSidebarEnabled())
            @include('adminlte::partials.navbar.menu-item-right-sidebar-toggler')
        @endif
    </ul>

</nav>