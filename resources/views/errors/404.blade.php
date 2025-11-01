<x-guest-layout>
    <div class="flex min-h-screen">

        <!-- Sidebar -->






        <!-- Main content -->
        <div class="flex-1 flex flex-col min-w-0 ">





            <!-- Page content -->
            <main class="flex-1 flex items-center justify-center px-4 py-12">
                <div class="text-center max-w-lg">
                    <div class="text-blue-600 text-9xl font-extrabold mb-4">404</div>
                    <h2 class="text-3xl font-semibold text-gray-800 mb-2">Oops! Page Not Found</h2>
                    <p class="text-gray-600 mb-6">
                        The page you are looking for might have been removed, renamed, or does not exist.
                    </p>

                    @auth
                    <a href="{{ route('dashboard') }}"
                        class="inline-block px-6 py-3 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 transition">
                        Return to Dashboard
                    </a>
                    @else
                    <a href="{{ route('home') }}"
                        class="inline-block px-6 py-3 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 transition">
                        Return to Home
                    </a>
                    @endauth
                </div>
            </main>





        </div>
    </div>
</x-guest-layout>