<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\LoanController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    
    // Authentication routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    Route::post('/refresh-token', [AuthController::class, 'refreshToken']);
    
    // Book routes
    Route::apiResource('books', BookController::class);
    Route::get('/books-available', [BookController::class, 'available']);
    Route::get('/books-statistics', [BookController::class, 'statistics']);
    
    // Category routes
    Route::apiResource('categories', CategoryController::class);
    Route::get('/categories-tree', [CategoryController::class, 'tree']);
    
    // Loan routes
    Route::get('/loans', [LoanController::class, 'index']);
    Route::post('/loans', [LoanController::class, 'store']); // Borrow book
    Route::get('/loans/{loan}', [LoanController::class, 'show']);
    Route::post('/loans/{loan}/return', [LoanController::class, 'returnBook']);
    Route::post('/loans/{loan}/extend', [LoanController::class, 'extend']);
    Route::get('/my-loans', [LoanController::class, 'myLoans']);
    
    // Admin/Librarian routes
    Route::middleware('role:librarian|admin')->group(function () {
        Route::get('/loans-overdue', [LoanController::class, 'overdue']);
        Route::post('/loans-mark-overdue', [LoanController::class, 'markOverdue']);
        Route::get('/loans-statistics', [LoanController::class, 'statistics']);
    });
});

// Health check route
Route::get('/health', function () {
    return response()->json([
        'status' => 'OK',
        'timestamp' => now(),
        'version' => '1.0.0'
    ]);
});