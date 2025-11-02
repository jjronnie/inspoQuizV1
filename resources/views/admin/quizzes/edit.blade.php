<x-app-layout>

    <x-page-title title="Edit Quiz: {{ $quiz->title }}"  />

    <div class=" mx-auto bg-white p-6 md:p-8 shadow-xl rounded-lg border border-gray-100">
        
        <form method="POST" action="{{ route('admin.quizzes.update', $quiz) }}">
            @csrf
            @method('PUT')

            <!-- Quiz Title -->
            <div class="mb-5">
                <x-input-label for="title" :value="__('Quiz Title')" />
                <x-text-input id="title" class="block mt-1 w-full" type="text" name="title" :value="old('title', $quiz->title)" placeholder="e.g., Introduction to Laravel Development" required autofocus />
                <x-input-error :messages="$errors->get('title')" class="mt-2" />
            </div>

            <!-- Description -->
            <div class="mb-5">
                <x-input-label for="description" :value="__('Description')" />
                <textarea id="description" name="description" rows="4" 
                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                    placeholder="Provide a brief description of the quiz contents or topics.">{{ old('description', $quiz->description) }}</textarea>
                <x-input-error :messages="$errors->get('description')" class="mt-2" />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <!-- Time Limit -->
                <div class="mb-5">
                    <x-input-label for="time_limit_minutes" :value="__('Time Limit (Minutes)')" />
                    <x-text-input id="time_limit_minutes" class="block mt-1 w-full" type="number" name="time_limit_minutes" :value="old('time_limit_minutes', $quiz->time_limit_minutes)" required min="1" />
                    <x-input-error :messages="$errors->get('time_limit_minutes')" class="mt-2" />
                    <p class="text-xs text-gray-500 mt-1">The maximum time allowed for users to complete the quiz.</p>
                </div>

                <!-- Publish Status -->
                <div class="mb-5 flex items-center pt-8">
                    <input id="is_published" type="checkbox" name="is_published" value="1" 
                           @checked(old('is_published', $quiz->is_published)) 
                           class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                    <x-input-label for="is_published" :value="__('Publish Quiz')" class="ml-2 text-sm text-gray-600" />
                    <x-input-error :messages="$errors->get('is_published')" class="mt-2" />
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end mt-4 pt-4 border-t border-gray-200">
              <a  href="{{ route('admin.quizzes.index') }}" class=" btn-gray mr-3">
                    Cancel
                </a>

                <x-primary-button>
                    {{ __('Update Quiz') }}
                </x-primary-button>
            </div>
        </form>
    </div>

</x-app-layout>
