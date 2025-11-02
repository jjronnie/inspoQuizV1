<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Question; // Imported Question Model
use App\Models\Answer;   // Imported Answer Model
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;




class QuizController extends Controller
{

// public function __construct()
// {
//     $this->middleware(['auth', 'role:admin']);
// }


    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Fetch all quizzes, showing latest first
        $quizzes = Quiz::latest()->with('creator')->paginate(500);

        return view('admin.quizzes.index', compact('quizzes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Return a view for the creation form
        return view('admin.quizzes.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'time_limit_minutes' => 'required|integer|min:1',
            'is_published' => 'boolean',
        ]);

        $quiz = Quiz::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'time_limit_minutes' => $validated['time_limit_minutes'],
            'is_published' => $validated['is_published'] ?? false,
            'created_by' => Auth::id(), // Assign the current admin as creator
        ]);

        // Redirect to the show page to start adding questions
        return redirect()->route('admin.quizzes.show', $quiz)
            ->with('success', 'Quiz created successfully! Now add questions.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Quiz $quiz)
    {
        // Eager load questions and their answers for the management view
        $quiz->load('questions.answers');

        return view('admin.quizzes.show', compact('quiz'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Quiz $quiz)
    {
        return view('admin.quizzes.edit', compact('quiz'));
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Quiz $quiz)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'time_limit_minutes' => 'required|integer|min:1',
            'is_published' => 'boolean',
        ]);

    

        $quiz->update($validated);

        return redirect()->route('admin.quizzes.index')
            ->with('success', 'Quiz updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Quiz $quiz)
    {
        $quiz->delete();

        return redirect()->route('admin.quizzes.index')
            ->with('success', 'Quiz deleted successfully.');
    }

    public function storeQuestion(Request $request, Quiz $quiz)
    {
        // 1. Validation for Question and nested Answers
        $validated = $request->validate([
            'text' => 'required|string',
            'points' => 'required|integer|min:1',
            'answers' => 'required|array|min:2', // Must have at least two answer options
            'answers.*.text' => 'required|string|max:255',
            'answers.*.is_correct' => 'boolean',
        ]);

        // 2. Custom validation: Must have exactly one correct answer
        $correct_count = collect($validated['answers'])->filter(fn($answer) => $answer['is_correct'] === true)->count();

        if ($correct_count !== 1) {
            throw ValidationException::withMessages([
                'answers' => ['You must designate exactly one correct answer.'],
            ]);
        }

        // 3. Create the Question linked to the Quiz
        $question = $quiz->questions()->create([
            'text' => $validated['text'],
            'points' => $validated['points'],
        ]);

        // 4. Create the Answers
        $answers = collect($validated['answers'])->map(function ($answer) use ($question) {
            return new Answer([
                'text' => $answer['text'],
                'is_correct' => $answer['is_correct'],
            ]);
        });

        $question->answers()->saveMany($answers);

        return redirect()->route('admin.quizzes.show', $quiz)
            ->with('success', 'Question and answers added successfully.');
    }

    /**
     * Update the specified Question and synchronize its Answers.
     */
    public function updateQuestion(Request $request, Quiz $quiz, Question $question)
    {
        // Ensure the question belongs to the quiz (implicit via route model binding, but good practice)
        if ($question->quiz_id !== $quiz->id) {
            abort(404);
        }

        // 1. Validation for Question and nested Answers
        $validated = $request->validate([
            'text' => 'required|string',
            'points' => 'required|integer|min:1',
            'answers' => 'required|array|min:2', // Must have at least two answer options
            'answers.*.text' => 'required|string|max:255',
            'answers.*.is_correct' => 'boolean',
        ]);

        // 2. Custom validation: Must have exactly one correct answer
        $correct_count = collect($validated['answers'])->filter(fn($answer) => $answer['is_correct'] === true)->count();

        if ($correct_count !== 1) {
            throw ValidationException::withMessages([
                'answers' => ['You must designate exactly one correct answer.'],
            ]);
        }

        // 3. Update the Question
        $question->update([
            'text' => $validated['text'],
            'points' => $validated['points'],
        ]);

        // 4. Synchronize Answers: Delete existing, then create new set
        $question->answers()->delete();

        $answers = collect($validated['answers'])->map(function ($answer) {
            return new Answer([
                'text' => $answer['text'],
                'is_correct' => $answer['is_correct'],
            ]);
        });

        $question->answers()->saveMany($answers);

        return redirect()->route('admin.quizzes.show', $quiz)
            ->with('success', 'Question and answers updated successfully.');
    }

    /**
     * Remove the specified Question.
     */
    public function destroyQuestion(Quiz $quiz, Question $question)
    {
        // Ensure the question belongs to the quiz
        if ($question->quiz_id !== $quiz->id) {
            abort(404);
        }

        // Deleting the question automatically deletes its answers due to cascade in migration
        $question->delete();

        return redirect()->route('admin.quizzes.show', $quiz)
            ->with('success', 'Question deleted successfully.');
    }



}
