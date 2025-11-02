<x-guest-layout>
    
    @php
        // ---------------------------------------------------------------------
        // PHP Setup (Actual Data - MUST be passed from the controller)
        // Ensure $quiz and $questions are available in the view.
        // ---------------------------------------------------------------------

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

        <!-- Header and Timer Panel (STICKY) -->
        <!-- The 'sticky top-0 z-50' class makes this header stay at the top of the viewport -->
        <div class="bg-indigo-700 text-white p-6 rounded-t-xl shadow-lg flex justify-between items-center sticky top-0 z-50">
            <h1 class="text-2xl font-extrabold">{{ $quiz->title }}</h1>
            
            <!-- Timer Display -->
            <div x-cloak x-show="!attemptFinished" class="flex items-center space-x-2 bg-indigo-800 p-3 rounded-xl shadow-md">
                <i data-lucide="clock" class="w-6 h-6"></i>
                <span class="text-lg font-mono" x-text="formatTime(timeRemaining)"></span>
            </div>
            
            <div x-cloak x-show="attemptFinished" class="flex items-center space-x-2 bg-green-500 p-3 rounded-xl shadow-md">
                <i data-lucide="check-circle" class="w-6 h-6"></i>
                <span class="text-lg font-semibold"> Finished!</span>
            </div>
        </div>

        <div class="bg-white p-6 md:p-8 shadow-2xl rounded-b-xl border border-indigo-200">

            <!-- QUIZ INTRODUCTION / NOT STARTED STATE -->
            <div x-cloak x-show="!quizStarted">
                <p class="mb-4">Once the time elapses, the quiz will auto submit.</p>
                <p class="mb-4">Once you select an answer, you won't be able to select again.</p>
             
                <button @click="startQuiz()" class="btn">
                    Start Quiz Now
                </button>
            </div>
            
            <!-- QUIZ ATTEMPT STATE -->
            <div x-cloak x-show="quizStarted && !attemptFinished">

                <!-- Questions List (ALL QUESTIONS) -->
                <div class="space-y-10">
                    <template x-for="(q, qIndex) in questions" :key="q.id">
                        <div :id="'q_' + q.id" class="border-b pb-8">
                            <!-- Question Title -->
                            <h4 class="text-xl font-bold mb-4 text-blue-800 flex justify-between items-start">
                                <span>
                                    <span x-text="qIndex + 1"></span>. <span x-html="q.text"></span>
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
                                            
                                            // User Selected Correct Answer (only correct if selected)
                                            'bg-green-100 border-green-500 shadow-md': q.answered && answer.is_correct && answer.selected,
                                            
                                            // Disabled state when answered
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
                                    <span class="font-semibold flex items-center"><i data-lucide="thumbs-down" class="w-5 h-5 mr-2"></i> Incorrect. Don't worry, we have highlighted the correct answer for you.</span>
                                </template>
                            </div>

                        </div>
                    </template>
                </div>

                <!-- Submission Control -->
                <div class="flex justify-center items-center pt-8 mt-8 border-t">
                    <button @click="submitQuiz()" class="btn-success flex items-center">
                        <i data-lucide="send" class="w-5 h-5 mr-2"></i> Finish & Submit Quiz
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
                
                <p class="text-center mt-8 text-lg text-blue-700">Thank you for completing the quiz.</p>
                
                <!-- Share and Retry Area -->
                <div class="flex flex-col sm:flex-row justify-center items-center mt-6 space-y-4 sm:space-y-0 sm:space-x-4">
                    
                    <!-- Retry Button -->
                    <a href="/" class="btn w-full sm:w-auto">Retry</a> 

                    <!-- Share Section -->
                    <div class="flex space-x-3 p-2 border rounded-full bg-gray-50 shadow-inner">
                        <span class="text-sm text-gray-600 font-medium self-center pl-2 hidden md:block">Share Your Score:</span>

                        <!-- X / Twitter -->
                        <a :href="`https://twitter.com/intent/tweet?text=${shareText}&url=${appUrl}`" 
                           target="_blank" 
                           class="p-2 bg-gray-900 rounded-full text-white hover:bg-gray-700 transition duration-150"
                           title="Share on X">
                            <!-- Custom X icon using SVG -->
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="0" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x">
                                <path d="M18.901 1.153h3.684l-8.04 9.19h8.224l-11.4 13.6h-7.09l8.605-10.375h-7.653l10.82-12.445h-6.233l-.79 1.483l-.791-1.483h-6.233z" />
                            </svg>
                        </a>

                        <!-- Facebook -->
                        <a :href="`https://www.facebook.com/sharer/sharer.php?u=${appUrl}&quote=${shareText}`" 
                           target="_blank" 
                           class="p-2 bg-blue-700 rounded-full text-white hover:bg-blue-600 transition duration-150"
                           title="Share on Facebook">
                            <i data-lucide="facebook" class="w-4 h-4 fill-white"></i>
                        </a>

                        <!-- WhatsApp -->
                        <a :href="`https://api.whatsapp.com/send?text=${shareText}%20${appUrl}`" 
                           target="_blank" 
                           class="p-2 bg-green-600 rounded-full text-white hover:bg-green-500 transition duration-150"
                           title="Share on WhatsApp">
                            <i data-lucide="message-square-text" class="w-4 h-4"></i>
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>
    
    <!-- Alpine Logic -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('quizAttempt', (config) => ({
                questions: [],
                timeLimit: config.timeLimit,
                timeRemaining: config.timeLimit,
                timerInterval: null,
                quizStarted: false,
                attemptFinished: false,
                finalScore: 0,
                appUrl: window.location.origin + '/', // Base URL for sharing

                init() {
                    // Deep copy and initialize properties
                    this.questions = config.questionsData.map(q => ({
                        ...q,
                        answered: false,      // Track if the user has selected an answer
                        user_correct: null,   // Tracks: true, false, or null (unattempted)
                        answers: q.answers.map(a => ({
                            ...a,
                            selected: false, // Track if the answer was selected by the user
                        }))
                    }));
                    // Initial rendering of Lucide icons
                    lucide.createIcons();
                },

                // --- COMPUTED PROPERTIES ---

                get allQuestionsAnswered() {
                    return this.questions.every(q => q.answered);
                },
                
                get correctAnswersCount() {
                    return this.questions.filter(q => q.user_correct === true).length;
                },

                // Share Text Generator
                get shareText() {
                    // Template: "Hi, I scored [correctAnswersCount]/[questions.length] in this quiz, please try it out!"
                    const scoreString = `${this.correctAnswersCount}/${this.questions.length}`;
                    const message = `Hi, I scored ${scoreString} in this quiz, please try it out!`;
                    return encodeURIComponent(message);
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
                        // Important: Re-run icon rendering for the share icons that use Alpine bindings
                        this.$nextTick(() => {
                            lucide.createIcons();
                        });
                        // In a real app: POST final answers to server
                    }
                },

                submitQuiz() {
                    // Check all questions before submitting
                    if (!this.allQuestionsAnswered) {
                        alert('Please answer ALL questions before finishing and submitting the quiz.');
                        return;
                    }
                    
                    // Confirmation before submitting
                    if (confirm('Are you sure you want to finish and submit the quiz?')) {
                        this.autoSubmit();
                    }
                },

                // Score Calculation
                calculateScore() {
                    this.finalScore = this.questions.reduce((total, q) => {
                        if (q.user_correct === true) {
                            return total + q.points;
                        }
                        return total;
                    }, 0);
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
                    });
                    
                    // Force Alpine to re-render the question
                    this.$nextTick(() => {
                        const qElement = document.getElementById('q_' + question.id);
                        if(qElement) {
                            lucide.createIcons({ scope: qElement });
                        }
                    });
                },
            }));
        });
    </script>
</x-guest-layout>