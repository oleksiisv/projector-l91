<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TransactionController;

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
Route::get('/', function () {
    return view('welcome');
});

Route::get('/transactions', [TransactionController::class, 'list']);
Route::get('/transaction/create', [TransactionController::class, 'create']);
Route::get('/transaction/view', [TransactionController::class, 'view']);
Route::get('/transaction/sample', [TransactionController::class, 'sample']);
