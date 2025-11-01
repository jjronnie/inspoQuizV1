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
    <!-- Generic Error Display (for non-JS fallback or critical errors) -->
    @if ($errors->any() && !request()->acceptsJson())
        <div class="p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg" role="alert">
            <p class="font-bold">Please correct the errors below:</p>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
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

    @php
        // Prepare all data for Alpine in a PHP block to avoid Blade parsing errors in attributes
        $initData = [
            'questions' => $quiz->questions, // Will be auto-JSON-encoded
            'storeUrl' => route('admin.quizzes.questions.store', $quiz),
            'updateUrlTemplate' => route('admin.quizzes.questions.update', [$quiz, ':questionId']),
            'deleteUrlTemplate' => route('admin.quizzes.questions.destroy', [$quiz, ':questionId']),
            'errors' => $errors->any() ? $errors->toArray() : new stdClass(), // Use empty object
            'oldText' => old('text', ''),
            'oldPoints' => old('points', 1),
            'oldAnswers' => old('answers'), // Pass the old array directly
        ];
    @endphp

    <!-- Main Question Manager Component -->
    <div 
        x-data="quizManager" 
        x-init="init(
            {{ json_encode($initData) }}
        )"
        class="max-w-7xl mx-auto space-y-8"
    >
        
        <!-- === ADD NEW QUESTION FORM === -->
        <div class="bg-white p-6 md:p-8 shadow-xl rounded-xl border border-indigo-200">
            <h3 class="text-2xl font-bold text-indigo-700 mb-6 flex items-center">
                <i data-lucide="plus-circle" class="w-6 h-6 mr-2"></i> Add New Question
            </h3>

            <!-- This form is now submitted via fetch() in Alpine, not a standard POST -->
            <form x-ref="createForm" @submit.prevent="submitCreateForm" class="space-y-6">
                @csrf

                <!-- Question Text and Points -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div class="md:col-span-3">
                        <x-input-label for="new_text" :value="__('Question Text')" />
                        <textarea id="new_text" x-model="newQuestion.text" name="text" rows="3" 
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            placeholder="Type the main question here..." required></textarea>
                        <template x-if="errors.text"><p class="text-sm text-red-600 mt-1" x-text="errors.text[0]"></p></template>
                    </div>
                    <div>
                        <x-input-label for="new_points" :value="__('Points/Marks')" />
                        <x-text-input id="new_points" x-model.number="newQuestion.points" type="number" name="points" min="1" required class="mt-1 block w-full" />
                        <template x-if="errors.points"><p class="text-sm text-red-600 mt-1" x-text="errors.points[0]"></p></template>
                    </div>
                </div>

                <!-- Answer Options Management -->
                <h4 class="text-lg font-semibold mt-6 flex items-center text-gray-700">
                    <i data-lucide="check-square" class="w-5 h-5 mr-2"></i> Answer Options (Must have 1 correct)
                </h4>
                
                <div class="space-y-3 p-4 border border-gray-200 rounded-lg bg-gray-50">
                    <template x-for="(answer, index) in newQuestion.answers" :key="index">
                        <div class="flex items-center space-x-3 bg-white p-3 rounded-lg shadow-sm border border-gray-100">
                            <!-- Correct Answer Checkbox -->
                            <div class="flex items-center">
                                <!-- Note: We use a hidden input and x-model on the checkbox -->
                                <input type="hidden" :name="'answers[' + index + '][is_correct]'" :value="answer.is_correct ? 1 : 0">
                                <input type="checkbox" :id="'new_correct_' + index" 
                                    x-model="answer.is_correct"
                                    @change="setCorrectAnswer(index)" 
                                    class="w-5 h-5 text-green-600 border-gray-300 rounded focus:ring-green-500">
                                <label :for="'new_correct_' + index" class="ml-2 text-sm text-gray-700 font-medium whitespace-nowrap">Correct</label>
                            </div>

                            <!-- Answer Text Input -->
                            <x-text-input 
                                :id="'new_text_' + index" 
                                x-model="answer.text" 
                                :name="'answers[' + index + '][text]'" 
                                type="text" 
                                placeholder="Answer option text" 
                                required 
                                class="flex-1" />

                            <!-- Remove Button -->
                            <button type="button" @click="removeAnswer(index)" 
                                class="p-2 text-red-600 hover:text-red-800 rounded-full transition duration-150"
                                :disabled="newQuestion.answers.length <= 2"
                                :class="{'opacity-50 cursor-not-allowed': newQuestion.answers.length <= 2}"
                                title="Remove Answer">
                                <i data-lucide="x" class="w-5 h-5"></i>
                            </button>
                        </div>
                    </template>
                    
                    <template x-if="errors.answers"><p class="text-sm text-red-600 mt-1" x-text="errors.answers[0]"></p></template>
                    <template x-if="!errors.answers && errors['answers.0.text']"><p class="text-sm text-red-600 mt-1">Please ensure all answer texts are filled out.</p></template>

                    <!-- Add Answer Button -->
                    <x-secondary-button type="button" @click="addAnswer()" class="mt-4 w-full justify-center text-indigo-600 border-indigo-300 hover:bg-indigo-50">
                        <i data-lucide="plus" class="w-4 h-4 mr-2"></i> Add Option
                    </x-secondary-button>
                </div>
                
                <!-- Submit Button -->
                <div class="flex justify-end pt-4 border-t border-gray-100">
                    <x-primary-button type="submit" x-bind:disabled="isSubmitting" :class="{ 'opacity-25': isSubmitting }">
                        <span x-show="!isSubmitting">Save Question</span>
                        <span x-show="isSubmitting">Saving...</span>
                    </x-primary-button>
                </div>
            </form>
        </div>
        
        <!-- === EXISTING QUESTIONS LIST === -->
        <h2 class="text-2xl font-bold text-gray-800 pt-8 border-t border-gray-200">Existing Questions (Total: <span x-text="questions.length"></span>)</h2>

        <div class="space-y-4">
            <template x-for="(question, qIndex) in questions" :key="question.id">
                <div class="bg-white p-6 rounded-xl shadow-lg border-l-4 border-indigo-500 flex justify-between items-start space-x-6">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-indigo-600">Question #<span x-text="qIndex + 1"></span> | Points: <span x-text="question.points"></span></p>
                        <p class="text-lg font-semibold text-gray-900 mt-1" x-text="question.text"></p>
                        
                        <!-- Answers List -->
                        <div class="mt-3 space-y-2 text-sm">
                            <template x-for="answer in question.answers" :key="answer.id">
                                <div class="flex items-start">
                                    <i data-lucide="dot" class="w-4 h-4 mt-1 flex-shrink-0" :class="answer.is_correct ? 'text-green-500' : 'text-gray-400'"></i>
                                    <span :class="answer.is_correct ? 'font-semibold text-green-700' : 'text-gray-700'" x-text="answer.text"></span>
                                    <template x-if="answer.is_correct">
                                        <span class="ml-2 px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Correct</span>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-col space-y-2 flex-shrink-0">
                        <!-- Edit Button -->
                        <button type="button" @click="editQuestion(qIndex)" 
                            class="p-2 text-blue-600 hover:text-blue-900 rounded-lg bg-blue-100 transition duration-150 shadow-sm flex items-center"
                            title="Edit Question">
                            <i data-lucide="edit-3" class="w-5 h-5"></i>
                        </button>
                        <!-- Delete Button -->
                        <button type="button" @click="confirmDelete(question.id)"
                            class="p-2 text-red-600 hover:text-red-900 rounded-lg bg-red-100 transition duration-150 shadow-sm flex items-center"
                            title="Delete Question">
                            <i data-lucide="trash-2" class="w-5 h-5"></i>
                        </button>
                    </div>
                </div>
            </template>

            <template x-if="questions.length === 0">
                <div class="bg-white p-10 text-center rounded-xl shadow-lg text-gray-500">
                    <i data-lucide="clipboard-list" class="w-8 h-8 mx-auto mb-3"></i>
                    No questions have been added yet. Use the form above to start building your quiz!
                </div>
            </template>
        </div>

        <!-- === MODAL FOR EDITING QUESTION === -->
        <div x-cloak x-show="isEditing" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <!-- Overlay -->
            <div x-show="isEditing" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

            <!-- Modal Panel -->
            <div x-show="isEditing" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                @click.away="isEditing = false"
                class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full mx-auto mt-20">
                
                <form x-ref="editForm" @submit.prevent="submitUpdateForm" class="space-y-6">
                    @csrf
                    @method('PUT')
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                            <i data-lucide="edit" class="w-6 h-6 mr-2"></i> Edit Question
                        </h3>
                        
                        <!-- Question Text and Points (Editing) -->
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                            <div class="md:col-span-3">
                                <x-input-label for="edit_text" :value="__('Question Text')" />
                                <textarea id="edit_text" x-model="editingQuestion.text" name="text" rows="3" 
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    placeholder="Type the main question here..." required></textarea>
                                <template x-if="errors.text"><p class="text-sm text-red-600 mt-1" x-text="errors.text[0]"></p></template>
                            </div>
                            <div>
                                <x-input-label for="edit_points" :value="__('Points/Marks')" />
                                <x-text-input id="edit_points" x-model.number="editingQuestion.points" type="number" name="points" min="1" required class="mt-1 block w-full" />
                                <template x-if="errors.points"><p class="text-sm text-red-600 mt-1" x-text="errors.points[0]"></p></template>
                            </div>
                        </div>

                        <!-- Answer Options Management (Editing) -->
                        <h4 class="text-lg font-semibold mt-6 flex items-center text-gray-700">
                            <i data-lucide="check-square" class="w-5 h-5 mr-2"></i> Answer Options (Edit)
                        </h4>

                        <div class="space-y-3 p-4 border border-gray-200 rounded-lg bg-gray-50">
                            <template x-for="(answer, index) in editingQuestion.answers" :key="'edit-' + index">
                                <div class="flex items-center space-x-3 bg-white p-3 rounded-lg shadow-sm border border-gray-100">
                                    <!-- Correct Answer Checkbox -->
                                    <div class="flex items-center">
                                        <input type="hidden" :name="'answers[' + index + '][is_correct]'" :value="answer.is_correct ? 1 : 0">
                                        <input type="checkbox" :id="'edit_correct_' + index" 
                                            x-model="answer.is_correct"
                                            @change="setCorrectAnswer(index, true)" 
                                            class="w-5 h-5 text-green-600 border-gray-300 rounded focus:ring-green-500">
                                        <label :for="'edit_correct_' + index" class="ml-2 text-sm text-gray-700 font-medium whitespace-nowrap">Correct</label>
                                    </div>

                                    <!-- Answer Text Input -->
                                    <x-text-input 
                                        :id="'edit_text_' + index" 
                                        x-model="answer.text" 
                                        :name="'answers[' + index + '][text]'" 
                                        type="text" 
                                        placeholder="Answer option text" 
                                        required 
                                        class="flex-1" />

                                    <!-- Remove Button -->
                                    <button type="button" @click="removeAnswer(index, true)" 
                                        class="p-2 text-red-600 hover:text-red-800 rounded-full transition duration-150"
                                        :disabled="editingQuestion.answers.length <= 2"
                                        :class="{'opacity-50 cursor-not-allowed': editingQuestion.answers.length <= 2}"
                                        title="Remove Answer">
                                        <i data-lucide="x" class="w-5 h-5"></i>
                                    </button>
                                </div>
                            </template>

                            <!-- Error display for answers in edit modal -->
                            <template x-if="errors.answers"><p class="text-sm text-red-600 mt-1" x-text="errors.answers[0]"></p></template>

                            <!-- Add Answer Button -->
                            <x-secondary-button type="button" @click="addAnswer(true)" class="mt-4 w-full justify-center text-indigo-600 border-indigo-300 hover:bg-indigo-50">
                                <i data-lucide="plus" class="w-4 h-4 mr-2"></i> Add Option
                            </x-secondary-button>
                        </div>
                    </div>

                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <x-primary-button type="submit" x-bind:disabled="isSubmitting" :class="{ 'opacity-25': isSubmitting }" class="sm:ml-3">
                            <span x-show="!isSubmitting">Update Question</span>
                            <span x-show="isSubmitting">Updating...</span>
                        </x-primary-button>
                        <x-secondary-button type="button" @click="isEditing = false" class="mt-3 sm:mt-0">
                            Cancel
                        </x-secondary-button>
                    </div>
                </form>
            </div>
        </div>

        <!-- === DELETE CONFIRMATION MODAL (Question) === -->
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
                                        Are you sure you want to delete this question? This action cannot be undone and the question will be removed from the quiz.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <form @submit.prevent="submitDelete" class="inline-flex">
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

    <!-- Alpine.js Quiz Manager Logic -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('quizManager', () => ({
                questions: [],
                storeUrl: '',
                updateUrlTemplate: '',
                deleteUrlTemplate: '',
                isSubmitting: false,
                errors: {},
                
                // State for creating new questions
                newQuestion: {
                    text: '',
                    points: 1,
                    answers: [
                        { text: '', is_correct: true },
                        { text: '', is_correct: false },
                    ]
                },

                // State for editing existing questions
                isEditing: false,
                editingQuestion: null,
                editingQuestionIndex: null,

                // State for deletion
                openDeleteModal: false,
                deleteQuestionId: null,

                // NEW: Initialize data from the single JSON object
                init(initData) {
                    this.questions = initData.questions;
                    this.storeUrl = initData.storeUrl;
                    this.updateUrlTemplate = initData.updateUrlTemplate;
                    this.deleteUrlTemplate = initData.deleteUrlTemplate;
                    
                    // Pre-fill errors and old data if they exist
                    this.errors = initData.errors;
                    if (Object.keys(this.errors).length > 0) {
                        this.newQuestion.text = initData.oldText;
                        this.newQuestion.points = initData.oldPoints;
                        
                        if (initData.oldAnswers) {
                            this.newQuestion.answers = initData.oldAnswers.map(answer => ({
                                text: answer.text,
                                is_correct: answer.is_correct === '1' || answer.is_correct === true
                            }));
                        }
                    }
                },

                // --- Answer Management Methods (used by both create and edit) ---
                addAnswer(isEdit = false) {
                    const target = isEdit ? this.editingQuestion.answers : this.newQuestion.answers;
                    target.push({ text: '', is_correct: false });
                },

                removeAnswer(index, isEdit = false) {
                    const target = isEdit ? this.editingQuestion.answers : this.newQuestion.answers;
                    if (target.length > 2) {
                        // If removing the correct answer, make the first answer correct
                        if (target[index].is_correct && isEdit) {
                            this.setCorrectAnswer(0, true);
                        } else if (target[index].is_correct) {
                            this.setCorrectAnswer(0, false);
                        }
                        target.splice(index, 1);
                    }
                },

                setCorrectAnswer(index, isEdit = false) {
                    const targetAnswers = isEdit ? this.editingQuestion.answers : this.newQuestion.answers;
                    targetAnswers.forEach((answer, i) => {
                        answer.is_correct = (i === index);
                    });
                },

                // --- Form Submission Methods (using fetch) ---
                async submitCreateForm() {
                    this.isSubmitting = true;
                    this.errors = {};
                    
                    try {
                        const formData = new FormData(this.$refs.createForm);
                        const response = await fetch(this.storeUrl, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'Accept': 'application/json', // Request JSON response
                            },
                        });

                        const result = await response.json();

                        if (response.ok) {
                            window.location.reload(); // Simple reload to show success message
                        } else if (response.status === 422) {
                            this.errors = result.errors;
                        } else {
                            throw new Error(result.message || 'An unexpected error occurred.');
                        }
                    } catch (e) {
                        console.error(e);
                        this.errors = { 'form': [e.message] };
                    } finally {
                        this.isSubmitting = false;
                    }
                },

                async submitUpdateForm() {
                    this.isSubmitting = true;
                    this.errors = {};
                    
                    const updateUrl = this.updateUrlTemplate.replace(':questionId', this.editingQuestion.id);
                    
                    try {
                        const formData = new FormData(this.$refs.editForm);
                        
                        // Note: Fetch with FormData automatically handles file uploads,
                        // but for PUT, we send a POST and let Laravel handle the @method('PUT')
                        const response = await fetch(updateUrl, {
                            method: 'POST', // Use POST for FormData with _method
                            body: formData,
                            headers: {
                                'Accept': 'application/json',
                            },
                        });

                        const result = await response.json();

                        if (response.ok) {
                            window.location.reload(); 
                        } else if (response.status === 422) {
                            this.errors = result.errors;
                        } else {
                            throw new Error(result.message || 'An unexpected error occurred during update.');
                        }
                    } catch (e) {
                        console.error(e);
                        this.errors = { 'form': [e.message] };
                    } finally {
                        this.isSubmitting = false;
                    }
                },

                // --- Edit Methods ---
                editQuestion(qIndex) {
                    this.editingQuestionIndex = qIndex;
                    // Deep copy the question object to avoid mutating state
                    this.editingQuestion = JSON.parse(JSON.stringify(this.questions[qIndex])); 
                    this.isEditing = true;
                    this.errors = {}; // Clear errors
                    
                    this.$nextTick(() => {
                        document.getElementById('edit_text').focus();
                    });
                },

                // --- Delete Methods ---
                confirmDelete(questionId) {
                    this.deleteQuestionId = questionId;
                    this.openDeleteModal = true;
                },
                
                async submitDelete() {
                    if (!this.deleteQuestionId) return;

                    const deleteUrl = this.deleteUrlTemplate.replace(':questionId', this.deleteQuestionId);
                    
                    try {
                        const response = await fetch(deleteUrl, {
                            method: 'POST', // Use POST for forms
                            body: new FormData(event.target), // Pass the delete form
                            headers: {
                                'Accept': 'application/json',
                            },
                        });

                        if (response.ok) {
                            window.location.reload();
                        } else {
                            const result = await response.json();
                            throw new Error(result.message || 'Failed to delete.');
                        }
                    } catch (e) {
                        console.error(e);
                        alert(e.message); // Use a proper modal in production
                    } finally {
                        this.openDeleteModal = false;
                        this.deleteQuestionId = null;
                    }
                }
            }));
        });
    </script>
</x-app-layout>

