<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Console\AuthController;
use App\Http\Controllers\Console\PostController;
use App\Http\Controllers\Console\DashboardController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('', [HomeController::class, 'index'])->name('home.index');
Route::get('/posts/{id}', [HomeController::class, 'show'])->name('home.show');

Route::group(['prefix' => 'console', 'namespace' => 'Console'], function() {
    Route::get('login', [AuthController::class, 'index'])->name('login');
    Route::post('do-login', [AuthController::class, 'login'])->name('do-login');
    Route::get('registration', [AuthController::class, 'registration'])->name('registration');
    Route::post('do-registration', [AuthController::class, 'register'])->name('do-registration');

    Route::group(['middleware' => 'auth'], function() {
        Route::get('', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('logout', [AuthController::class, 'logout'])->name('logout');

        Route::group(['prefix' => 'posts', 'middleware' => 'can'], function() {
            Route::get('', [PostController::class, 'index'])->name('posts.index');
            Route::get('create', [PostController::class, 'create'])->name('posts.create');
            Route::post('', [PostController::class, 'store'])->name('posts.store');
            Route::get('{id}', [PostController::class, 'show'])->name('posts.show');
            Route::get('/update/{id}', [PostController::class, 'edit'])->name('posts.edit');
            Route::put('{id}', [PostController::class, 'update'])->name('posts.update');
            Route::put('/publish/{id}', [PostController::class, 'publish'])->name('posts.publish');
            Route::put('/unpushlish/{id}', [PostController::class, 'unPublish'])->name('posts.unpushlish');
            Route::delete('{id}', [PostController::class, 'delete'])->name('posts.delete');
        });
    });
});
