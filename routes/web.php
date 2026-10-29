<?php

use App\Http\Controllers\Admin\BlockController;
use App\Http\Controllers\Admin\CandidateController;
use App\Http\Controllers\Admin\ElectionController as AdminElectionController;
use App\Http\Controllers\Admin\PositionController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Voter\BallotController;
use App\Http\Controllers\Voter\ElectionController as VoterElectionController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::get('/admin/login', [AuthenticatedSessionController::class, 'createAdmin'])->name('admin.login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:6,1')->name('login.store');
    Route::post('/admin/login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:6,1')->name('admin.login.store');
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store'])->middleware('throttle:6,1')->name('register.store');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::prefix('voter')->name('voter.')->group(function (): void {
        Route::get('/elections', [VoterElectionController::class, 'index'])->name('elections');
        Route::get('/elections/{election}/ballot', [BallotController::class, 'show'])->name('ballot.show');
        Route::post('/elections/{election}/ballot', [BallotController::class, 'store'])->name('ballot.store');
    });
    Route::prefix('admin')->name('admin.')->middleware('role:admin,adviser,committee')->group(function (): void {
        Route::get('/blocks', [BlockController::class, 'index'])->name('blocks.index');
        Route::post('/blocks', [BlockController::class, 'store'])->name('blocks.store');
        Route::delete('/blocks/{block}', [BlockController::class, 'destroy'])->name('blocks.destroy');
        Route::get('/students', [StudentController::class, 'index'])->name('students.index');
        Route::post('/students/{user}/verify', [StudentController::class, 'verify'])->name('students.verify');
        Route::delete('/students/{user}/reject', [StudentController::class, 'reject'])->name('students.reject');
        Route::delete('/students/{user}', [StudentController::class, 'destroy'])->name('students.destroy');
        Route::get('/elections', [AdminElectionController::class, 'index'])->name('elections.index');
        Route::post('/elections', [AdminElectionController::class, 'store'])->name('elections.store');
        Route::put('/elections/{election}', [AdminElectionController::class, 'update'])->name('elections.update');
        Route::delete('/elections/{election}', [AdminElectionController::class, 'destroy'])->name('elections.destroy');
        Route::post('/elections/{election}/transition', [AdminElectionController::class, 'transition'])->name('elections.transition');
        Route::get('/elections/{election}/positions', [PositionController::class, 'index'])->name('positions.index');
        Route::post('/elections/{election}/positions', [PositionController::class, 'store'])->name('positions.store');
        Route::delete('/elections/{election}/positions/{position}', [PositionController::class, 'destroy'])->name('positions.destroy');
        Route::get('/elections/{election}/candidates', [CandidateController::class, 'index'])->name('candidates.index');
        Route::post('/elections/{election}/positions/{position}/candidates', [CandidateController::class, 'store'])->name('candidates.store');
        Route::delete('/elections/{election}/candidates/{candidate}', [CandidateController::class, 'destroy'])->name('candidates.destroy');
    });
});
