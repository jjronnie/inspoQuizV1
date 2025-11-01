<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuizController;

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});




Route::middleware(['auth', 'role:admin'])->name('admin.')->prefix('admin')->group(function () {
    
    // 1. Resource routes for Quiz CRUD (index, create, store, show, edit, update, destroy)
    Route::resource('quizzes', QuizController::class);

    // 2. Custom routes for Question management (nested under a specific Quiz)
    // POST to store a new question and its answers
    Route::post('quizzes/{quiz}/questions', [QuizController::class, 'storeQuestion'])
        ->name('quizzes.questions.store');

    // PUT/PATCH to update an existing question and its answers
    Route::put('quizzes/{quiz}/questions/{question}', [QuizController::class, 'updateQuestion'])
        ->name('quizzes.questions.update');
        
    // DELETE to remove a question and its associated answers
    Route::delete('quizzes/{quiz}/questions/{question}', [QuizController::class, 'destroyQuestion'])
        ->name('quizzes.questions.destroy');
});


Route::middleware(['auth', 'role:user'])->name('user.')->prefix('user')->group(function () {
    // ... Routes for viewing quizzes, starting attempts, and submitting
});


require __DIR__.'/auth.php';
