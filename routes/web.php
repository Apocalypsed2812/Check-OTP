<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Middleware;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Authentication
Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', function () {
    return view('login');
});

Route::get('/register', function() {
    return view('register');
});

Route::get('/otp', function(){
    return view('OTP');
})->middleware('check.auth');

Route::post('/register', [AuthController::class, 'register'])->name('register');

Route::post('/login', [AuthController::class, 'login'])->name('login');

Route::post('/otp', [AuthController::class, 'checkOTP']);

Route::post('/unactived-otp', [AuthController::class, 'unactivedOTP']);

// Admin
Route::get('/home', [AdminController::class, 'renderHome']);

Route::prefix('admin')->group(function(){
    Route::post('add-product', [AdminController::class, 'addProduct'])->name('addProduct');
    Route::post('delete-product', [AdminController::class, 'deleteProduct'])->name('deleteProduct');
    Route::post('edit-product', [AdminController::class, 'editProduct'])->name('editProduct');
});
