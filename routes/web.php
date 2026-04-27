<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\History\History;


Route::get('/', function () {
    return redirect()->route('dashboard');
})->name('home');


Route::livewire('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');


Route::livewire('/employee', 'employee.create')
    ->middleware(['auth', 'verified'])
    ->name('employee');


Route::livewire('/training', 'training')
    ->middleware(['auth', 'verified'])
    ->name('trainingdata');

Route::livewire('/user-management', 'user-management')
    ->middleware(['auth', 'verified'])
    ->name('user-management');

Route::livewire('/managementdata', 'managementdata')
    ->middleware(['auth', 'verified'])
    ->name('managementdata');

Route::livewire('/atributemanagement', 'atributemanagement.atributemanagement')
    ->middleware(['auth', 'verified'])
    ->name('atributemanagement');

Route::livewire('/trainingdetail', 'trainingdetail')
    ->middleware(['auth', 'verified'])
    ->name('trainingdetail');

Route::livewire('/trnc', 'trainer-contribution')
    ->middleware(['auth', 'verified'])
    ->name('trnc');

Route::livewire('/prf', 'my-profile')
    ->middleware(['auth', 'verified'])
    ->name('prf');

Route::livewire('/avg', 'average-training')
    ->middleware(['auth', 'verified'])
    ->name('avg');

Route::livewire('/trnp', 'training-penetration')
    ->middleware(['auth', 'verified'])
    ->name('trnp');

Route::livewire('/history', 'history.history')
    ->middleware(['auth', 'verified'])
    ->name('history');

require __DIR__ . '/settings.php';
