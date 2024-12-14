<?php

use App\Http\Controllers\LessonController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\ReportControler;
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

    // reports
    Route::get('/reports', [ReportControler::class, 'index'])->name('reports.index');
    Route::get('/reports/show/{id}', [ReportControler::class, 'show'])->name('reports.show');
    Route::post('/reports/export', [ReportControler::class, 'export'])->name('reports.export');
    Route::post('/reports/export-full', [ReportControler::class, 'exportFullReport'])->name('reports.exportFull');

    // notifications
    Route::get('/notifications', [App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/create', [App\Http\Controllers\NotificationController::class, 'create'])->name('notifications.create');
    Route::post('/notifications', [App\Http\Controllers\NotificationController::class, 'store'])->name('notifications.store');
    Route::get('/notifications/{id}', [App\Http\Controllers\NotificationController::class, 'show'])->name('notifications.show');
    Route::get('/notifications/{id}/edit', [App\Http\Controllers\NotificationController::class, 'edit'])->name('notifications.edit');
    Route::put('/notifications/{id}', [App\Http\Controllers\NotificationController::class, 'update'])->name('notifications.update');
    Route::delete('/notifications/{id}', [App\Http\Controllers\NotificationController::class, 'destroy'])->name('notifications.destroy');


});
