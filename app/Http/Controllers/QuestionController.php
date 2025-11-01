<?php

namespace App\Http\Controllers;

use App\Models\Question;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\Answer;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class QuestionController extends Controller
{ public function create(Quiz $quiz)
    {
        return view('admin.questions.create', compact('quiz'));
    }

    /**
     * Store a newly created question in storage.
     */
    public function store(Request $request, Quiz $quiz)
    {
        $validated = $request->validate([
            'text' => 'required|string|max:1000',
            'points' => 'required|integer|min:1',
            'answers' => 'required|array|min:2',
            'answers.*.text' => 'required|string|max:500',
            'answers.*.is_correct' => 'nullable|boolean',
        ]);

        // Custom validation: Ensure exactly one answer is correct
        $correctCount = collect($validated['answers'])->where('is_correct', true)->count();

        if ($correctCount !== 1) {
            throw ValidationException::withMessages([
                'answers' => 'You must select exactly one correct answer.'
            ]);
        }

        DB::transaction(function () use ($quiz, $validated) {
            // Create the Question
            $question = $quiz->questions()->create([
                'text' => $validated['text'],
                'points' => $validated['points'],
            ]);

            // Create the Answers
            foreach ($validated['answers'] as $answerData) {
                $question->answers()->create([
                    'text' => $answerData['text'],
                    'is_correct' => $answerData['is_correct'] ?? false,
                ]);
            }
        });

        return redirect()->route('admin.quizzes.show', $quiz)
                         ->with('success', 'Question added successfully!');
    }

    /**
     * Show the form for editing the specified question.
     */
    public function edit(Quiz $quiz, Question $question)
    {
        // Eager load the answers for the form
        $question->load('answers');
        
        return view('admin.questions.edit', compact('quiz', 'question'));
    }

    /**
     * Update the specified question in storage.
     */
    public function update(Request $request, Quiz $quiz, Question $question)
    {
        $validated = $request->validate([
            'text' => 'required|string|max:1000',
            'points' => 'required|integer|min:1',
            'answers' => 'required|array|min:2',
            'answers.*.text' => 'required|string|max:500',
            'answers.*.is_correct' => 'nullable|boolean',
        ]);

        // Custom validation: Ensure exactly one answer is correct
        $correctCount = collect($validated['answers'])->where('is_correct', true)->count();
        
        if ($correctCount !== 1) {
            throw ValidationException::withMessages([
                'answers' => 'You must select exactly one correct answer.'
            ]);
        }

        DB::transaction(function () use ($question, $validated) {
            // Update the Question
            $question->update([
                'text' => $validated['text'],
                'points' => $validated['points'],
            ]);

            // Simple "Delete and Recreate" strategy for answers
            // This is robust and avoids complex sync logic
            $question->answers()->delete();

            // Re-create the Answers
            foreach ($validated['answers'] as $answerData) {
                $question->answers()->create([
                    'text' => $answerData['text'],
                    'is_correct' => $answerData['is_correct'] ?? false,
                ]);
            }
        });

        return redirect()->route('admin.quizzes.show', $quiz)
                         ->with('success', 'Question updated successfully!');
    }

    /**
     * Remove the specified question from storage.
     */
    public function destroy(Quiz $quiz, Question $question)
    {
        // The Question model's deleting event will handle deleting answers.
        $question->delete();

        return redirect()->route('admin.quizzes.show', $quiz)
                         ->with('success', 'Question deleted successfully!');
    }
}
