<div class="w-80 lg:w-64 bg-primary text-white flex flex-col fixed top-0 left-0 h-screen z-40 lg:z-[10000] transform transition-transform duration-300 -translate-x-full lg:translate-x-0"
    id="sidebar">
    <!-- Sidebar Header -->

    <div class="sidebar-header">
        <div class="flex items-center space-x-3">
            <div class="w-24 h-24  rounded-lg flex items-center justify-center text-white font-bold text-lg">
                <span>
                    <a href="{{ url('/') }}">
                        <img src="{{ asset('logo.webp') }}" alt="LOGO">
                    </a>
                </span>
            </div>

        </div>
        <button class="lg:hidden p-1 rounded-md hover:bg-blue-900 transition-colors" id="closeSidebar">

            <i data-lucide="x" class="w-4 h-4 text-white"></i>
        </button>
    </div>

    <!-- Scrollable Navigation Area -->



    <div class="flex-1 overflow-y-auto no-scrollbar">
        <nav class="p-4 space-y-1">
            {{-- Dashboard --}}

            {{-- Dashboard --}}
            <a href="{{ route('dashboard') }}"
                class="sidebar-link {{ request()->routeIs('dashboard') ? 'sidebar-link-active' : '' }}">
                <i data-lucide="layout-dashboard" class="w-4 h-4 text-white"></i>
                <span>Dashboard</span>
            </a>
            <div class="space-y-1">





                <a href="#" class="sidebar-link">
                    <i data-lucide="question-mark" class="w-4 h-4 text-white"></i>
                    <span>Quiz</span>
                </a>


                {{-- Users --}}
                {{-- <a href="{{ route('superadmin.users.index') }}"
                    class="sidebar-link {{ request()->routeIs('superadmin.users.*') ? 'sidebar-link-active' : '' }}">
                    <i data-lucide="users" class="w-4 h-4 text-white"></i>
                    <span>Users</span>
                </a> --}}








            </div>
    </div>
    </nav>

</div>