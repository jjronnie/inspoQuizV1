<x-app-layout>

    <!-- Page Title should reflect the content -->
    <x-page-title title="Quiz Management"  />

    @if (session('success'))
    <div class="p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-lg" role="alert">
        {{ session('success') }}
    </div>
    @endif

    <!-- Controls -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
        <div class="flex flex-col sm:flex-row gap-4">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i data-lucide="search" class="w-4 h-4 text-gray-400"></i>
                </div>

                <input type="text" id="searchInput"
                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    placeholder="Search...">
            </div>
        </div>
        <div class="flex gap-3">
            <a class="btn "
                href="{{ route('admin.quizzes.create') }}">
                <i data-lucide="plus" class="w-4 h-4 mr-2"></i>Create New Quiz
            </a>

           
        </div>
    </div>

    <!-- Main Table -->
    <div class="bg-white shadow-xl sm:rounded-lg overflow-hidden" x-data="{ openDeleteModal: false, deleteUrl: '' }">
        <x-table :headers="['#', 'Title', 'Status', 'Time Limit (Mins)', 'Created At', 'By']" showActions="true">
            @forelse ($quizzes as $quiz)
            <x-table.row>
                <x-table.cell>{{ $loop->iteration + $quizzes->firstItem() - 1 }}</x-table.cell>

                <x-table.cell class="font-semibold text-indigo-600 hover:text-indigo-800 transition duration-150">
                    <!-- Link to the Quiz management/questions page -->
                    <a href="{{ route('admin.quizzes.show', $quiz) }}">{{ $quiz->title }}</a>
                </x-table.cell>

                <x-table.cell>
                    @if ($quiz->is_published)
                    <span
                        class="inline-flex items-center px-3 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        Published
                    </span>
                    @else
                    <span
                        class="inline-flex items-center px-3 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                        Draft
                    </span>
                    @endif
                </x-table.cell>

                <x-table.cell>{{ $quiz->time_limit_minutes }}</x-table.cell>
                <x-table.cell>{{ $quiz->created_at->format('M d, Y') }}</x-table.cell>
                <x-table.cell>{{ $quiz->creator->name ?? 'N/A' }}</x-table.cell>

                <x-table.cell>
                    <div class="flex gap-2">
                        <!-- View/Manage Questions Button -->
                        <a class="btn" href="{{ route('admin.quizzes.show', $quiz) }}" title="Manage Questions">
                            <i data-lucide="eye" class="w-4 h-4"></i>
                        </a>
                        <!-- Edit Button -->
                        <a class="btn" href="{{ route('admin.quizzes.edit', $quiz) }}" title="Edit Quiz Details">
                            <i data-lucide="edit-3" class="w-4 h-4"></i>
                        </a>


                        <x-confirm-modal :action="route('admin.quizzes.destroy', $quiz)"
                            warning="Are you sure you want to delete this Quiz? This action cannot be undone."
                            triggerIcon="trash" />

                    </div>
                </x-table.cell>
            </x-table.row>
            @empty
            <x-table.row>
                <x-table.cell colspan="7" class="text-center py-6 text-gray-500">
                    No quizzes found. Click "New Quiz" to get started.
                </x-table.cell>
            </x-table.row>
            @endforelse
        </x-table>

        <!-- Pagination Links -->
        <div class="p-4 bg-white border-t border-gray-200">
            {{ $quizzes->links() }}
        </div>



    </div>

</x-app-layout>