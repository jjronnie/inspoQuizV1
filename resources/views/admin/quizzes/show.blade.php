<x-app-layout>

    <!-- Page Title & Navigation -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <x-page-title title="Manage Questions: {{ $quiz->title }}" subtitle="Add, edit, and publish questions for this quiz." />
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('admin.quizzes.index') }}" class="btn text-gray-600 hover:text-gray-800 flex items-center">
                <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i> Back to Quizzes
            </a>
            <a href="{{ route('admin.quizzes.edit', $quiz) }}" class="btn bg-blue-100 text-blue-600 hover:bg-blue-200 flex items-center shadow-sm">
                <i data-lucide="settings" class="w-4 h-4 mr-2"></i> Edit Quiz Details
            </a>
        </div>
    </div>

    <!-- Session Messages -->
    @if (session('success'))
        <div class="p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-lg" role="alert">
            {{ session('success') }}
        </div>
    @endif
    <!-- Generic Error Display (Catches errors redirected from create/edit/delete actions) -->
    @if ($errors->any())
        <div class="p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg" role="alert">
            <p class="font-bold">An action failed.</p> Please check the relevant form page or console for details.
        </div>
    @endif

    <!-- Quiz Summary Card -->
    <div class="bg-white p-6 rounded-xl shadow-lg mb-8 border border-gray-100">
        <h3 class="text-xl font-bold text-gray-800 mb-3">Quiz Summary</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
            <div>
                <p class="font-medium text-gray-500">Status</p>
                @if ($quiz->is_published)
                    <span class="inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium bg-green-100 text-green-800">
                        <i data-lucide="check-circle" class="w-4 h-4 mr-1"></i> Published
                    </span>
                @else
                    <span class="inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                        <i data-lucide="eye-off" class="w-4 h-4 mr-1"></i> Draft
                    </span>
                @endif
            </div>
            <div>
                <p class="font-medium text-gray-500">Time Limit</p>
                <p class="text-gray-900">{{ $quiz->time_limit_minutes }} Minutes</p>
            </div>
            <div>
                <p class="font-medium text-gray-500">Total Questions</p>
                <p class="text-gray-900">{{ $quiz->questions->count() }}</p>
            </div>
            <div>
                <p class="font-medium text-gray-500">Total Points</p>
                <p class="text-gray-900">{{ $quiz->questions->sum('points') }}</p>
            </div>
        </div>
    </div>
    
    <!-- ADD QUESTION BUTTON -->
    <div class="text-right mb-6">
        {{-- Link to the dedicated create page --}}
        <a href="{{ route('admin.quizzes.questions.create', $quiz) }}" class="btn inline-flex items-center bg-indigo-600 text-white hover:bg-indigo-700 px-6 py-2 rounded-lg shadow-md transition duration-150">
            <i data-lucide="plus" class="w-5 h-5 mr-2"></i> Add New Question
        </a>
    </div>

    <!-- === EXISTING QUESTIONS LIST === -->
    <h2 class="text-2xl font-bold text-gray-800 pt-8 border-t border-gray-200">Existing Questions (Total: {{ $quiz->questions->count() }})</h2>

    <div class="space-y-4" x-data="{ openDeleteModal: false, deleteUrl: '' }">
        
        @forelse ($quiz->questions as $question)
            <div class="bg-white p-6 rounded-xl shadow-lg border-l-4 border-indigo-500 flex justify-between items-start space-x-6">
                <div class="flex-1">
                    <p class="text-sm font-medium text-indigo-600">Question #{{ $loop->iteration }} | Points: {{ $question->points }}</p>
                    <p class="text-lg font-semibold text-gray-900 mt-1">{{ $question->text }}</p>
                    
                    <!-- Answers List -->
                    <div class="mt-3 space-y-2 text-sm">
                        @foreach ($question->answers as $answer)
                            <div class="flex items-start">
                                <i data-lucide="dot" class="w-4 h-4 mt-1 flex-shrink-0 {{ $answer->is_correct ? 'text-green-500' : 'text-gray-400' }}"></i>
                                <span class="{{ $answer->is_correct ? 'font-semibold text-green-700' : 'text-gray-700' }}">{{ $answer->text }}</span>
                                @if ($answer->is_correct)
                                    <span class="ml-2 px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Correct</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex flex-col space-y-2 flex-shrink-0">
                    <!-- Edit Button (Links to new dedicated edit page) -->
                    <a href="{{ route('admin.quizzes.questions.edit', [$quiz, $question]) }}" 
                        class="p-2 text-blue-600 hover:text-blue-900 rounded-lg bg-blue-100 transition duration-150 shadow-sm flex items-center"
                        title="Edit Question">
                        <i data-lucide="edit-3" class="w-5 h-5"></i>
                    </a>
                    
                    <!-- Delete Button (Uses a simpler Alpine modal setup) -->
                    <button type="button" 
                        @click="deleteUrl = '{{ route('admin.quizzes.questions.destroy', [$quiz, $question]) }}'; openDeleteModal = true;"
                        class="p-2 text-red-600 hover:text-red-900 rounded-lg bg-red-100 transition duration-150 shadow-sm flex items-center"
                        title="Delete Question">
                        <i data-lucide="trash-2" class="w-5 h-5"></i>
                    </button>
                </div>
            </div>
        @empty
            <div class="bg-white p-10 text-center rounded-xl shadow-lg text-gray-500">
                <i data-lucide="clipboard-list" class="w-8 h-8 mx-auto mb-3"></i>
                No questions have been added yet. Click 'Add New Question' above to start building your quiz!
            </div>
        @endforelse

        <!-- === DELETE CONFIRMATION MODAL (Cleaned up Alpine) === -->
        <div x-cloak x-show="openDeleteModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <!-- Overlay -->
                <div x-show="openDeleteModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                    class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

                <!-- Modal Panel -->
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div x-show="openDeleteModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    @click.away="openDeleteModal = false"
                    class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <i data-lucide="alert-triangle" class="w-6 h-6 text-red-600"></i>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                    Confirm Question Deletion
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        Are you sure you want to delete this question? This action cannot be undone.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <form x-bind:action="deleteUrl" method="POST" class="inline-flex">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                                Delete Question
                            </button>
                        </form>
                        <button type="button" @click="openDeleteModal = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
