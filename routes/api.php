<?php

use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [UserController::class, 'register'])->name('users.register');
Route::post('/login', [UserController::class, 'login'])->name('users.login');
Route::get('/', function(){
    return response()->json(['message' => 'Test']);
});
// Protected Routes (Require Token)
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [UserController::class, 'logout'])->name('users.logout');

    Route::group(['prefix' => 'user'], function () {
        Route::get('/', [UserController::class, 'getLoggedInUser'])->name('user.loggedInUser');
        Route::post('/update', [UserController::class, 'updateUser'])->name('user.updateUser');
    });

    Route::middleware('role_or_above:admin')->group(function () {
        Route::post('/change-role', [UserController::class, 'changeUserRole'])->name('users.changeUserRole');
    });

    Route::middleware('role_or_above:manager')->group(function () {

        Route::group(['prefix' => 'users'], function () {
            Route::get('/', [UserController::class, 'getUsers'])->name('users.getUsers');
            Route::get('/{user}', [UserController::class, 'getUser'])->name('users.getUser');
        });

        Route::group(['prefix' => 'tasks'], function () {
            Route::post('/create', [TaskController::class, 'store'])->name('tasks.create');
            Route::post('/{id}/update', [TaskController::class, 'update'])->name('tasks.update');
            Route::post('/{id}/add-dependents', [TaskController::class, 'addDependents'])->name('tasks.addDependents');
        });
    });

    Route::group(['prefix' => 'tasks'], function () {
        Route::get('/', [TaskController::class, 'index'])->name('tasks.getTasks');
        Route::get('/{id}', [TaskController::class, 'show'])->name('tasks.getById');
        Route::post('/{id}/change-status', [TaskController::class, 'changeStatus'])->name('tasks.changeStatus');
    });
});
