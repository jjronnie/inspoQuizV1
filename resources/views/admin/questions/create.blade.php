<x-app-layout>
    @php
        $route = route('admin.quizzes.questions.store', $quiz);
        $method = 'POST';

        // Default answers
        $answers = old('answers', [
            ['text' => '', 'is_correct' => true],
            ['text' => '', 'is_correct' => false],
        ]);
    @endphp

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">Add Question to: {{ $quiz->title }}</h1>
        <a href="{{ route('admin.quizzes.show', $quiz) }}" class="text-indigo-600 hover:underline">Back to Questions</a>
    </div>

    <div x-data="{
            answers: {{ json_encode($answers) }},
            addAnswer() { this.answers.push({text:'', is_correct:false}) },
            removeAnswer(index) { if(this.answers.length>2) this.answers.splice(index,1) },
            setCorrectAnswer(index) {
                this.answers.forEach((a,i)=>a.is_correct=(i===index));
            }
        }"
        class="bg-white p-6 rounded shadow border border-gray-200">

        <form action="{{ $route }}" method="POST" class="space-y-6">
            @csrf
            @method($method)

            <!-- Question Text -->
            <div>
                <label for="text" class="block font-medium text-gray-700">Question Text</label>
                <textarea id="text" name="text" rows="3" required
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('text') }}</textarea>
                @error('text')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <!-- Points -->
            <div>
                <label for="points" class="block font-medium text-gray-700">Points/Marks</label>
                <input type="number" id="points" name="points" min="1" value="{{ old('points',1) }}"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                @error('points')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <!-- Answers -->
            <div>
                <h3 class="font-semibold text-gray-700 mb-2">Answer Options (1 correct)</h3>
                <template x-for="(answer, index) in answers" :key="index">
                    <div class="flex items-center space-x-2 mb-2">
                        <!-- Correct Checkbox -->
                        <input type="hidden" :name="`answers[${index}][is_correct]`" :value="answer.is_correct ? 1 : 0">
                        <input type="checkbox" :id="`correct_${index}`" x-model="answer.is_correct"
                            @change="setCorrectAnswer(index)" class="w-5 h-5 text-green-600 border-gray-300 rounded">
                        <label :for="`correct_${index}`" class="text-sm text-gray-700">Correct</label>

                        <!-- Answer Text -->
                        <input type="text" class="flex-1 border-gray-300 rounded-md shadow-sm" placeholder="Answer text"
                            :name="`answers[${index}][text]`" x-model="answer.text" required>

                        <!-- Remove Button -->
                        <button type="button" @click="removeAnswer(index)" 
                            class="text-red-600 hover:text-red-800 px-2 py-1 rounded"
                            :disabled="answers.length<=2" :class="{'opacity-50 cursor-not-allowed': answers.length<=2}">
                            &times;
                        </button>
                    </div>
                </template>

                @error('answers')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                @error('answers.*.text')<p class="text-sm text-red-600">Please fill all answer texts.</p>@enderror

                <!-- Add Answer -->
                <button type="button" @click="addAnswer()"
                    class="mt-2 px-4 py-2 bg-indigo-100 text-indigo-700 rounded hover:bg-indigo-200">
                    + Add Option
                </button>
            </div>

            <!-- Submit -->
            <div class="pt-4 border-t border-gray-100">
                <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                    Save Question
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
