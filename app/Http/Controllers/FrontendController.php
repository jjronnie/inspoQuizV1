<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\Quiz;
class FrontendController extends Controller
{

       public function index()
    {
        // Fetch all quizzes, showing latest first
        $publishedQuizzes = Quiz::where('is_published', true)->get();

        return view('welcome', compact('publishedQuizzes'));
    }



 public function attempt(string $slug): View
{
    $quiz = Quiz::where('slug', $slug)->firstOrFail();

    $questions = $quiz->questions()
        ->with(['answers' => fn($query) => $query->inRandomOrder()])
        ->inRandomOrder()
        ->get();

    return view('attempt', [
        'quiz' => $quiz,
        'questions' => $questions,
    ]);
}

}


