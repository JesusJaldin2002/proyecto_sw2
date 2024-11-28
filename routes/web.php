<?php

use App\Http\Controllers\LessonController;
use App\Http\Controllers\ModuleController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;


Route::get('/', function () {
    if (Auth::check()) {
        return redirect('/home');
    }
    return redirect('/login');
});

Auth::routes();
Route::middleware(['auth'])->group(function () {

    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

    // modules
    Route::get('/modules/edit/{id}',[ModuleController::class,'edit'])->name('modules.edit');
    Route::put('/module',[ModuleController::class,'update'])->name('modules.update');
    Route::post('/module/disable/{id}',[ModuleController::class,'disable'])->name('modules.disable');
    Route::post('/module/enable/{id}',[ModuleController::class,'enable'])->name('modules.enable');
    Route::get('/modules/show/{id}',[ModuleController::class,'show'])->name('modules.show');

    // lessons
    Route::get('/lessons/show/{id}',[LessonController::class,'show'])->name('lessons.show');
    Route::get('/lessons/next/{id}', [LessonController::class, 'next'])->name('lessons.next');

});
