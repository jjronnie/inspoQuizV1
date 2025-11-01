<x-app-layout>
    


    @php
        // ---------------------------------------------------------------------
        // PHP Setup (MOCK DATA FOR DEMO PURPOSES)
        // In a real application, replace this with data passed from the controller.
        // The structure MUST match this format for Alpine.js to work.
        // ---------------------------------------------------------------------

        $quiz = $quiz ?? (object)[
            'title' => 'Sample Laravel Quiz',
            'description' => 'A practice quiz to test your Blade and Alpine skills.',
            'time_limit_minutes' => 30, // 30 minutes limit
            'total_questions' => 6,
        ];

        // Ensure the questions are structured for Alpine.js
        $questions = $questions ?? collect([
            (object)[
                'id' => 1, 'text' => 'What command is used to start a new Laravel project?', 'points' => 10, 
                'answers' => collect([
                    (object)['id' => 101, 'text' => 'laravel new project-name', 'is_correct' => true],
                    (object)['id' => 102, 'text' => 'composer create-project laravel/laravel', 'is_correct' => false],
                    (object)['id' => 103, 'text' => 'php artisan install', 'is_correct' => false],
                    (object)['id' => 104, 'text' => 'npm create-laravel-app', 'is_correct' => false],
                ])->shuffle()
            ],
            (object)[
                'id' => 2, 'text' => 'Which Blade directive defines the end of a conditional statement?', 'points' => 15, 
                'answers' => collect([
                    (object)['id' => 201, 'text' => '@endif', 'is_correct' => true],
                    (object)['id' => 202, 'text' => '@end', 'is_correct' => false],
                    (object)['id' => 203, 'text' => '@stop', 'is_correct' => false],
                    (object)['id' => 204, 'text' => '@close', 'is_correct' => false],
                ])->shuffle()
            ],
            (object)[
                'id' => 3, 'text' => 'What is the default view engine in Laravel?', 'points' => 5, 
                'answers' => collect([
                    (object)['id' => 301, 'text' => 'Twig', 'is_correct' => false],
                    (object)['id' => 302, 'text' => 'Blade', 'is_correct' => true],
                    (object)['id' => 303, 'text' => 'Smarty', 'is_correct' => false],
                    (object)['id' => 304, 'text' => 'Pug', 'is_correct' => false],
                ])->shuffle()
            ],
            (object)[
                'id' => 4, 'text' => 'Which method is used for defining a POST route?', 'points' => 10, 
                'answers' => collect([
                    (object)['id' => 401, 'text' => 'Route::get()', 'is_correct' => false],
                    (object)['id' => 402, 'text' => 'Route::post()', 'is_correct' => true],
                    (object)['id' => 403, 'text' => 'Route::send()', 'is_correct' => false],
                ])->shuffle()
            ],
            (object)[
                'id' => 5, 'text' => 'Which helper function is commonly used for debugging in Laravel?', 'points' => 10, 
                'answers' => collect([
                    (object)['id' => 501, 'text' => 'print_r()', 'is_correct' => false],
                    (object)['id' => 502, 'text' => 'dd()', 'is_correct' => true],
                    (object)['id' => 503, 'text' => 'dump()', 'is_correct' => false],
                ])->shuffle()
            ],
            (object)[
                'id' => 6, 'text' => 'What is the name of Laravel’s ORM?', 'points' => 15, 
                'answers' => collect([
                    (object)['id' => 601, 'text' => 'Eloquent', 'is_correct' => true],
                    (object)['id' => 602, 'text' => 'Doctrine', 'is_correct' => false],
                    (object)['id' => 603, 'text' => 'ActiveRecord', 'is_correct' => false],
                    (object)['id' => 604, 'text' => 'Propel', 'is_correct' => false],
                ])->shuffle()
            ],
        ]);

        // Convert the PHP collection to a JSON string for Alpine initialization
        $jsonQuestions = json_encode($questions);
        $timeLimitSeconds = $quiz->time_limit_minutes * 60;
    @endphp

    <!-- Quiz Attempt Interface -->
    <div x-data="quizAttempt({ 
            questionsData: {{ $jsonQuestions }}, 
            timeLimit: {{ $timeLimitSeconds }} 
        })" 
        x-init="init()"
        class="container mx-auto p-4 md:p-8">

        <!-- Header and Timer Panel -->
        <div class="bg-indigo-700 text-black p-6 rounded-t-xl shadow-lg flex justify-between items-center sticky top-0 z-10">
            <h1 class="text-3xl font-extrabold">{{ $quiz->title }}</h1>
            
            <div x-cloak x-show="!attemptFinished" class="flex items-center space-x-2 bg-indigo-800 p-3 rounded-xl shadow-md">
                <i data-lucide="clock" class="w-6 h-6"></i>
                <span class="text-lg font-mono" x-text="formatTime(timeRemaining)"></span>
            </div>
            
            <div x-cloak x-show="attemptFinished" class="flex items-center space-x-2 bg-green-500 p-3 rounded-xl shadow-md">
                <i data-lucide="check-circle" class="w-6 h-6"></i>
                <span class="text-lg font-semibold">Quiz Finished!</span>
            </div>
        </div>

        <div class="bg-white p-6 md:p-8 shadow-2xl rounded-b-xl border border-indigo-200">

            <!-- QUIZ INTRODUCTION / NOT STARTED STATE -->
            <div x-cloak x-show="!quizStarted">
                <h2 class="text-2xl font-bold mb-4">Instructions</h2>
                <p class="mb-4">{{ $quiz->description }}</p>
                <ul class="list-disc list-inside space-y-2 text-blue-600 mb-6">
                    <li><strong class="font-semibold text-red-600">Time Limit:</strong> {{ $quiz->time_limit_minutes }} minutes. The quiz will auto-submit when the time runs out.</li>
                    <li><strong class="font-semibold">Questions per Page:</strong> 3 questions.</li>
                    <li><strong class="font-semibold">Marking:</strong> Answers are marked **immediately** upon selection.</li>
                    <li><strong class="font-semibold">Selection Lock:</strong> Once an answer is selected for a question, it **cannot be changed**.</li>
                </ul>
                <button @click="startQuiz()" class="btn-primary py-3 px-6 text-xl shadow-lg transition duration-300 transform hover:scale-105">
                    Start Quiz Now
                </button>
            </div>
            
            <!-- QUIZ ATTEMPT STATE -->
            <div x-cloak x-show="quizStarted && !attemptFinished">

                <!-- Pagination Indicator -->
                <div class="text-center text-sm text-blue-500 mb-6">
                    Page <span x-text="currentPage"></span> of <span x-text="totalPages"></span>
                </div>

                <!-- Questions List -->
                <div class="space-y-10">
                    <template x-for="(q, qIndex) in paginatedQuestions" :key="q.id">
                        <div :id="'q_' + q.id" class="border-b pb-8">
                            <!-- Question Title -->
                            <h4 class="text-xl font-bold mb-4 text-blue-800 flex justify-between items-start">
                                <span>
                                    <span x-text="q.displayIndex"></span>. <span x-html="q.text"></span>
                                </span>
                                <span class="text-sm font-medium text-indigo-600 bg-indigo-100 px-3 py-1 rounded-full whitespace-nowrap">
                                    <span x-text="q.points"></span> pts
                                </span>
                            </h4>

                            <!-- Answer Options -->
                            <div class="space-y-2">
                                <template x-for="(answer, aIndex) in q.answers" :key="answer.id">
                                    <div 
                                        class="p-3 rounded-lg border-2 cursor-pointer transition-all duration-200"
                                        :class="{
                                            // Default state
                                            'bg-white hover:bg-indigo-50 border-blue-200': !q.answered, 
                                            
                                            // Correct Answer (after selection)
                                            'bg-green-100 border-green-500 shadow-md': q.answered && answer.is_correct,
                                            
                                            // User Selected Wrong Answer
                                            'bg-red-100 border-red-500 shadow-md': q.answered && !answer.is_correct && answer.selected,
                                            
                                            // User Selected Correct Answer
                                            'bg-green-100 border-green-500 shadow-md': q.answered && answer.is_correct && answer.selected,
                                            
                                            // Disabled state
                                            'cursor-not-allowed opacity-70': q.answered && !answer.selected && !answer.is_correct,
                                        }"
                                        @click="selectAnswer(q, answer)"
                                    >
                                        <p class="font-medium" x-html="answer.text"></p>
                                        <div x-show="q.answered" class="mt-1 text-sm font-semibold flex items-center">
                                            <template x-if="answer.is_correct">
                                                <span class="text-green-700 flex items-center">
                                                    <i data-lucide="check" class="w-4 h-4 mr-1"></i> Correct Answer
                                                </span>
                                            </template>
                                            <template x-if="!answer.is_correct && answer.selected">
                                                <span class="text-red-700 flex items-center">
                                                    <i data-lucide="x" class="w-4 h-4 mr-1"></i> Your Answer
                                                </span>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>
                            
                            <!-- Overall Feedback after selection -->
                            <div x-show="q.answered" class="mt-4 p-3 rounded-lg"
                                :class="{'bg-green-50 text-green-700 border border-green-300': q.user_correct, 'bg-red-50 text-red-700 border border-red-300': q.user_correct === false}">
                                <template x-if="q.user_correct">
                                    <span class="font-semibold flex items-center"><i data-lucide="thumbs-up" class="w-5 h-5 mr-2"></i> That's right! You earned <span x-text="q.points"></span> points.</span>
                                </template>
                                <template x-if="q.user_correct === false">
                                    <span class="font-semibold flex items-center"><i data-lucide="thumbs-down" class="w-5 h-5 mr-2"></i> Incorrect. The correct answer is highlighted in green.</span>
                                </template>
                            </div>

                        </div>
                    </template>
                </div>

                <!-- Pagination Controls -->
                <div class="flex justify-between items-center pt-8 mt-8 border-t">
                    <button @click="prevPage()" :disabled="currentPage === 1" 
                        class="btn-secondary flex items-center disabled:opacity-50 disabled:cursor-not-allowed">
                        <i data-lucide="arrow-left" class="w-5 h-5 mr-2"></i> Previous
                    </button>
                    <div class="text-sm font-medium text-blue-600">
                        Total Answered: <span x-text="totalAnswered"></span> / <span x-text="questions.length"></span>
                    </div>
                    <button @click="nextPage()" :disabled="currentPage === totalPages"
                        class="btn-primary flex items-center disabled:opacity-50 disabled:cursor-not-allowed"
                        x-show="currentPage < totalPages">
                        Next <i data-lucide="arrow-right" class="w-5 h-5 ml-2"></i>
                    </button>
                    <button @click="submitQuiz()" x-show="currentPage === totalPages" 
                        class="btn-success flex items-center">
                        <i data-lucide="send" class="w-5 h-5 mr-2"></i> Finish & Submit
                    </button>
                </div>
            </div>

            <!-- QUIZ FINISHED STATE -->
            <div x-cloak x-show="attemptFinished">
                <h2 class="text-3xl font-bold text-center text-green-700 mb-6">Quiz Submission Complete!</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-center">
                    <div class="bg-indigo-50 p-6 rounded-xl shadow-md border-b-4 border-indigo-500">
                        <p class="text-sm text-indigo-600 font-semibold">Total Questions</p>
                        <p class="text-4xl font-extrabold text-indigo-800" x-text="questions.length"></p>
                    </div>
                    <div class="bg-green-50 p-6 rounded-xl shadow-md border-b-4 border-green-500">
                        <p class="text-sm text-green-600 font-semibold">Your Score</p>
                        <p class="text-4xl font-extrabold text-green-800" x-text="finalScore"></p>
                    </div>
                    <div class="bg-blue-50 p-6 rounded-xl shadow-md border-b-4 border-blue-300">
                        <p class="text-sm text-blue-600 font-semibold">Time Taken</p>
                        <p class="text-4xl font-extrabold text-blue-800" x-text="formatTime(timeLimit - timeRemaining)"></p>
                    </div>
                </div>
                
                <p class="text-center mt-8 text-lg text-blue-700">Thank you for completing the quiz. You can now review your answers above.</p>
                <div class="flex justify-center mt-6">
                    <!-- Example button to return to quiz list -->
                    <a href="#" class="btn-primary">Return to Quiz List</a> 
                </div>
            </div>

        </div>
    </div>
    
    <!-- Alpine Logic -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('quizAttempt', (config) => ({
                questions: [],
                currentPage: 1,
                pageSize: 3,
                timeLimit: config.timeLimit,
                timeRemaining: config.timeLimit,
                timerInterval: null,
                quizStarted: false,
                attemptFinished: false,
                finalScore: 0,

                init() {
                    // Deep copy the initial questions array to make them reactive
                    this.questions = JSON.parse(JSON.stringify(config.questionsData));
                    // Initial rendering of Lucide icons
                    lucide.createIcons();
                },

                // --- COMPUTED PROPERTIES ---

                get totalPages() {
                    return Math.ceil(this.questions.length / this.pageSize);
                },

                get totalAnswered() {
                    return this.questions.filter(q => q.answered).length;
                },

                get paginatedQuestions() {
                    const start = (this.currentPage - 1) * this.pageSize;
                    const end = start + this.pageSize;
                    
                    const paged = this.questions.slice(start, end);
                    
                    // Add a displayIndex property for display purposes (1-based index)
                    return paged.map((q, index) => ({
                        ...q,
                        displayIndex: start + index + 1
                    }));
                },

                // --- TIMER & CONTROL METHODS ---

                startQuiz() {
                    this.quizStarted = true;
                    this.startTimer();
                },

                startTimer() {
                    this.timerInterval = setInterval(() => {
                        if (this.timeRemaining > 0 && !this.attemptFinished) {
                            this.timeRemaining--;
                        } else {
                            this.autoSubmit();
                        }
                    }, 1000);
                },

                formatTime(seconds) {
                    const minutes = Math.floor(seconds / 60);
                    const remainingSeconds = seconds % 60;
                    
                    const pad = (num) => String(num).padStart(2, '0');
                    
                    return `${pad(minutes)}:${pad(remainingSeconds)}`;
                },

                autoSubmit() {
                    clearInterval(this.timerInterval);
                    if (!this.attemptFinished) {
                        this.calculateScore();
                        this.attemptFinished = true;
                        // In a real app: POST final answers to server
                    }
                },

                submitQuiz() {
                    // Confirmation before submitting
                    if (confirm('Are you sure you want to finish and submit the quiz?')) {
                        this.autoSubmit();
                    }
                },

                calculateScore() {
                    this.finalScore = this.questions.reduce((total, q) => {
                        if (q.user_correct === true) {
                            return total + q.points;
                        }
                        return total;
                    }, 0);
                },

                // --- PAGINATION METHODS ---

                nextPage() {
                    if (this.currentPage < this.totalPages) {
                        this.currentPage++;
                        // Scroll to top of the quiz area for better UX
                        document.querySelector('.container').scrollIntoView({ behavior: 'smooth' });
                    }
                },

                prevPage() {
                    if (this.currentPage > 1) {
                        this.currentPage--;
                         // Scroll to top of the quiz area for better UX
                        document.querySelector('.container').scrollIntoView({ behavior: 'smooth' });
                    }
                },

                // --- ANSWER SELECTION AND MARKING ---

                selectAnswer(question, selectedAnswer) {
                    if (question.answered || this.attemptFinished) {
                        return; // Lock selection if already answered or quiz is over
                    }

                    // 1. Mark Question as Answered/Locked
                    question.answered = true;
                    
                    // 2. Determine Correctness
                    const isCorrect = selectedAnswer.is_correct;
                    question.user_correct = isCorrect;

                    // 3. Update Answers State (Visual feedback)
                    question.answers.forEach(answer => {
                        // Highlight user's selected answer
                        if (answer.id === selectedAnswer.id) {
                            answer.selected = true;
                        }
                        // Lock all answers for this question
                        answer.locked = true;
                    });
                    
                    // Force Alpine to re-render the question
                    this.$nextTick(() => {
                        // Re-run lucide icons only for the affected question (optional optimization)
                        const qElement = document.getElementById('q_' + question.id);
                        if(qElement) {
                            lucide.createIcons({ scope: qElement });
                        }
                    });
                },
            }));
        });
    </script>
</x-app-layout>
