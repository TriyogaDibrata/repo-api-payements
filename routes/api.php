<?php

use App\Http\Controllers\API\PackagesController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//API route for register new user
Route::post('/register', [App\Http\Controllers\API\AuthController::class, 'register']);
//API route for login user
Route::post('/login', [App\Http\Controllers\API\AuthController::class, 'login']);

//Protecting Routes
Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::get('/user', function(Request $request) {
        return auth()->user();
    });
    
    //Packages
    Route::resource('/packages', App\Http\Controllers\API\PackagesController::class);
    
    //Customer
    Route::resource('/customers', App\Http\Controllers\API\CustomersController::class);

    //Users
    Route::resource('/users', App\Http\Controllers\API\UserController::class);

    //Payments
    Route::get('/payments-customers', [App\Http\Controllers\API\PaymentsController::class, 'getCustomers']);
    Route::get('/payments-detail', [App\Http\Controllers\API\PaymentsController::class, 'paymentsDetail']);
    Route::post('/payments-create', [App\Http\Controllers\API\PaymentsController::class, 'createPayments']);
    Route::delete('/payments-destroy/{id}', [App\Http\Controllers\API\PaymentsController::class, 'deletePayments']);

    // API route for logout user
    Route::post('/logout', [App\Http\Controllers\API\AuthController::class, 'logout']);

    //Update Password
    Route::put('/update-password', [App\Http\Controllers\API\UserController::class, 'updatePassword']);
    
    //Dashboard
    Route::get('/dashboard', [App\Http\Controllers\API\DashboardController::class, 'getData']);
});

Route::get('/packages-ref', [App\Http\Controllers\API\RefController::class, 'getPackages']);
