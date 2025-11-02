<x-app-layout>

    <!-- Page Title & Navigation -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <x-page-title title="Manage Quiz: {{ $quiz->title }}" />
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('admin.quizzes.index') }}" class="btn-gray">
                <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i> Back to Quizzes
            </a>
            <a href="{{ route('admin.quizzes.edit', $quiz) }}" class="btn ">
                <i data-lucide="edit" class="w-4 h-4 mr-2"></i> Edit Quiz 
            </a>
        </div>
    </div>

    <!-- Session Messages -->
    @if (session('success'))
        <div class="p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-lg" role="alert">
            {{ session('success') }}
        </div>
    @endif
    @if ($errors->any())
        <div class="p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg" role="alert">
               {{ session('error') }}
        </div>
    @endif

    <!-- Quiz Summary Card -->
    <div class="bg-white p-6 rounded-xl  mb-8 border border-gray-100">
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
        <a href="{{ route('admin.quizzes.questions.create', $quiz) }}" class="btn ">
            <i data-lucide="plus" class="w-5 h-5 mr-2"></i> Add New Question
        </a>
    </div>

    <!-- === EXISTING QUESTIONS LIST === -->
    <h2 class="text-2xl font-bold mb-4 text-gray-800 pt-8 border-t border-gray-200"> Questions (Total: {{ $quiz->questions->count() }})</h2>

    
        
        @forelse ($quiz->questions as $question)
            <div class="bg-white p-6 mb-4 rounded-xl  border-l-4 border-indigo-500 flex justify-between items-start space-x-6">
                <div class="flex-1">
                    <p class="text-sm font-medium text-indigo-600">Question #{{ $loop->iteration }} | Points: {{ $question->points }}</p>
                    <p class="text-lg font-semibold text-gray-900 mt-1">{{ $question->text }}</p>
                    
                    <!-- Answers List -->
                    <div class="mt-3 space-y-2 text-sm">
                        @foreach ($question->answers as $answer)
                            <div class="flex items-start">
                                <i data-lucide="check" class="w-5 h-5 mt-1 flex-shrink-0 {{ $answer->is_correct ? 'text-green-500' : 'text-gray-400' }}"></i>
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
                        class="btn"
                        title="Edit Question">
                        <i data-lucide="edit-3" class="w-5 h-5"></i>
                    </a>
                    
               

                      <x-confirm-modal :action="route('admin.quizzes.questions.destroy', [$quiz, $question])"
                            warning="Are you sure you want to delete this Question? This action cannot be undone."
                            triggerIcon="trash" />
                </div>
            </div>
        @empty
         
            <x-empty-state message=" No questions have been added yet." />
        @endforelse


</x-app-layout>
