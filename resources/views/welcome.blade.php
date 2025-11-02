<x-guest-layout>
<div class="bg-gray-100 flex items-center  justify-center min-h-screen p-4">

    
@forelse ($publishedQuizzes as $quiz)
  <div class="bg-white shadow-xl rounded-2xl w-full max-w-md m-4 text-center p-6 sm:p-8">
    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">{{ $quiz->title }}</h1>
    <p class="text-gray-600 mb-6">{{ $quiz->description }}</p>

    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-md text-left mb-6">
      <h2 class="font-semibold text-yellow-700 mb-2 text-center">Quiz rules</h2>
      <ul class="list-disc list-inside text-sm text-gray-700 space-y-1">
        <li>You will answer all available questions.</li>
        <li>You have {{ $quiz->time_limit_minutes ?? '5'}} minutes to complete the quiz.</li>
        <li>You cannot go back to previous questions.</li>
        <li>Your score will be shown at the end.</li>
      </ul>
    </div>

    <p class="text-gray-800 mb-4 text-sm sm:text-base">
      A new quiz is posted every <span class="font-semibold">Week!</span> By
      <a href="www.inspo.techtowerinc.com" class="text-blue-600 font-semibold hover:underline">{{ config('app.name') }} Team</a>
    </p>

     <a href="{{ route('attempt', $quiz->slug ?? $quiz->id) }}" class="btn">
                                Attempt Quiz
                                <i data-lucide="arrow-right" class="w-4 h-4 inline ml-2"></i>
                            </a>
  </div>

         @empty
                <!-- No Quizzes Found State -->
                <div class="lg:col-span-3 text-center p-12 bg-white rounded-xl shadow-2xl border-4 border-dashed border-gray-300">
                    <i data-lucide="clipboard-list" class="w-16 h-16 mx-auto text-gray-400 mb-4"></i>
                    <h2 class="text-2xl font-bold text-gray-800">No Quizzes Available Yet</h2>
                    <p class="mt-2 text-gray-600 max-w-md mx-auto">
                        It looks like the administrator hasn't published any quizzes for you to attempt yet. 
                        Please check back later!
                    </p>
                   
                </div>
            @endforelse

</div>
</x-guest-layout>
