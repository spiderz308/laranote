<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FolderController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/public/notes/{note}', [NoteController::class, 'show'])
    ->name('notes.public');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/notes/trash', [NoteController::class, 'trash'])->name('notes.trash');
    Route::post('/notes/{note}/restore', [NoteController::class, 'restore'])->name('notes.restore')->withTrashed();
    Route::delete('/notes/{note}/force-delete', [NoteController::class, 'forceDelete'])->name('notes.forceDelete')->withTrashed();
    Route::resource('notes', NoteController::class);
    Route::get('/notes/{note}/share', [NoteController::class, 'share'])->name('notes.share');
    Route::put('/notes/{note}/collaborators', [NoteController::class, 'syncCollaborators'])->name('notes.collaborators.sync');
    Route::get('/notes/{note}/versions/{version}', [NoteController::class, 'showVersion'])->name('notes.versions.show');
    Route::post('/notes/{note}/versions/{version}/restore', [NoteController::class, 'restoreVersion'])->name('notes.versions.restore');
    Route::resource('folders', FolderController::class);
});

Route::get('/deploy/{token}', function ($token) {
  abort_unless($token === env('DEPLOY_TOKEN'), 403);
  
    // 1. Run Migrations & Clear Cache
    Artisan::call('migrate', ['--force' => true]);
    Artisan::call('optimize:clear');
    
    // 2. Fix Storage Link (The Custom Fix)
    // We point to the 'public_html' folder using $_SERVER['DOCUMENT_ROOT']
    $targetFolder = storage_path('app/public');
    $linkFolder = $_SERVER['DOCUMENT_ROOT'] . '/storage';
    
    if (!file_exists($linkFolder)) {
        symlink($targetFolder, $linkFolder);
        $storageStatus = 'Storage link created successfully.';
    } else {
        $storageStatus = 'Storage link already exists.';
    }
    
    return "Deployment completed.<br>" .
        "Migrations run.<br>" .
        "Cache cleared.<br>" .
        $storageStatus;
});

require __DIR__.'/auth.php';
