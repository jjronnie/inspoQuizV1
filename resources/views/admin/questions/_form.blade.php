<x-app-layout>

    @php
        // Configuration for the Create form
        $isEdit = false;
        $route = route('admin.quizzes.questions.store', $quiz);
        $method = 'POST';

        // Default answers for a new question
        $defaultAnswers = [
            (object)['text' => '', 'is_correct' => true],
            (object)['text' => '', 'is_correct' => false],
        ];

        // Use old input if validation failed, otherwise use defaults
        $answers = old('answers') 
            ? collect(old('answers'))->map(function($ans) {
                return (object) [
                    'text' => $ans['text'], 
                    'is_correct' => isset($ans['is_correct']) && $ans['is_correct'] == '1'
                ];
              })
            : $defaultAnswers;
    @endphp

    <!-- Page Title & Navigation -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <x-page-title title="Add Question to: {{ $quiz->title }}" subtitle="Define the question text and set the correct answer." />
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('admin.quizzes.show', $quiz) }}" class="btn text-gray-600 hover:text-gray-800 flex items-center">
                <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i> Back to Questions
            </a>
        </div>
    </div>

    <!-- Question Form -->
    <div x-data="questionForm({
        initialAnswers: {{ json_encode($answers) }}
    })" class="bg-white p-6 md:p-8 shadow-xl rounded-xl border border-indigo-200">

        <form action="{{ $route }}" method="POST" class="space-y-6">
            @csrf
            @method($method)

            <h3 class="text-2xl font-bold text-indigo-700 mb-6 flex items-center">
                <i data-lucide="plus-circle" class="w-6 h-6 mr-2"></i>
                Add New Question
            </h3>

            <!-- Question Text and Points -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="md:col-span-3">
                    <x-input-label for="text" :value="__('Question Text')" />
                    <textarea id="text" name="text" rows="3" 
                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                        placeholder="Type the main question here..." required>{{ old('text') }}</textarea>
                    @error('text')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <x-input-label for="points" :value="__('Points/Marks')" />
                    <x-text-input id="points" type="number" name="points" min="1" required 
                        value="{{ old('points', 1) }}" 
                        class="mt-1 block w-full" />
                    @error('points')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <!-- Answer Options Management -->
            <h4 class="text-lg font-semibold mt-6 flex items-center text-gray-700">
                <i data-lucide="check-square" class="w-5 h-5 mr-2"></i> Answer Options (Must have 1 correct)
            </h4>
            
            <div class="space-y-3 p-4 border border-gray-200 rounded-lg bg-gray-50">
                <template x-for="(answer, index) in answers" :key="index">
                    <div class="flex items-center space-x-3 bg-white p-3 rounded-lg shadow-sm border border-gray-100">
                        <!-- Correct Answer Checkbox -->
                        <div class="flex items-center">
                            <input type="hidden" :name="`answers[${index}][is_correct]`" :value="answer.is_correct ? 1 : 0">
                            
                            <input type="checkbox" :id="`correct_${index}`" 
                                x-model="answer.is_correct"
                                @change="setCorrectAnswer(index)" 
                                class="w-5 h-5 text-green-600 border-gray-300 rounded focus:ring-green-500">
                            <label :for="`correct_${index}`" class="ml-2 text-sm text-gray-700 font-medium whitespace-nowrap">Correct</label>
                        </div>

                        <!-- Answer Text Input -->
                        <x-text-input 
                            :id="`text_${index}`" 
                            x-model="answer.text" 
                            :name="`answers[${index}][text]`" 
                            type="text" 
                            placeholder="Answer option text" 
                            required 
                            class="flex-1" />

                        <!-- Remove Button -->
                        <button type="button" @click="removeAnswer(index)" 
                            class="p-2 text-red-600 hover:text-red-800 rounded-full transition duration-150"
                            :disabled="answers.length <= 2"
                            :class="{'opacity-50 cursor-not-allowed': answers.length <= 2}"
                            title="Remove Answer">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>
                </template>
                
                @error('answers')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                @error('answers.*.text')<p class="text-sm text-red-600 mt-1">Please ensure all answer texts are filled out.</p>@enderror

                <!-- Add Answer Button -->
                <x-secondary-button type="button" @click="addAnswer()" class="mt-4 w-full justify-center text-indigo-600 border-indigo-300 hover:bg-indigo-50">
                    <i data-lucide="plus" class="w-4 h-4 mr-2"></i> Add Option
                </x-secondary-button>
            </div>
            
            <!-- Submit Button -->
            <div class="flex justify-end pt-4 border-t border-gray-100">
                <x-primary-button type="submit">
                    Save Question
                </x-primary-button>
            </div>
        </form>
    </div>

    <!-- Alpine.js Script Block -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('questionForm', (config) => ({
                answers: [],
                
                init() {
                    this.answers = config.initialAnswers.map(answer => ({
                        text: answer.text,
                        is_correct: !!answer.is_correct
                    }));
                },

                addAnswer() {
                    this.answers.push({ text: '', is_correct: false });
                },

                removeAnswer(index) {
                    if (this.answers.length > 2) {
                        if (this.answers[index].is_correct) {
                            this.answers.splice(index, 1);
                            if (this.answers.length > 0) {
                                 this.setCorrectAnswer(0); 
                            }
                        } else {
                            this.answers.splice(index, 1);
                        }
                    }
                },

                setCorrectAnswer(index) {
                    this.answers.forEach((answer, i) => {
                        answer.is_correct = (i === index);
                    });
                },
            }));
        });
    </script>
</x-app-layout>
