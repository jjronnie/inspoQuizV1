<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\FrontendController;

use Illuminate\Support\Facades\Route;



 Route::get('/', [FrontendController::class, 'index'])->name('home');
 Route::get('/quizzes/{quiz}/attempt', [FrontendController::class, 'attempt'])->name('attempt');

 Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified', 'role:admin'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});




Route::middleware(['auth', 'verified', 'role:admin'])->name('admin.')->prefix('admin')->group(function () {
    
   
    // 1. Resource routes for Quiz CRUD (index, create, store, show, edit, update, destroy)
    Route::resource('quizzes', QuizController::class);

   
 


         Route::resource('quizzes.questions', QuestionController::class)
        ->except(['index', 'show']); // We don't need index/show for questions
        // Note: The controller implies nested routes (e.g., /quizzes/{quiz}/questions/{question}/edit)
        // so we don't need shallow()
});


Route::middleware(['auth', 'role:user'])->name('user.')->prefix('user')->group(function () {
    // ... Routes for viewing quizzes, starting attempts, and submitting
});


require __DIR__.'/auth.php';
