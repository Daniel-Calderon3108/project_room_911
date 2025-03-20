<?php

use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\HistoryAccessController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

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

/* ------------------------------User Routes -------------------------------   */
// Route to the login page
Route::get('/', [UserController::class, 'index'])->name('login');
// Route to the login action
Route::post('/login', [UserController::class, 'login']);
// Route to the logout action
Route::post('/logout', [UserController::class, 'logout'])->middleware('auth');
// Enable / Disable User
Route::put('/changeActive/{idUser}', [UserController::class, 'enableOrDisable'])->middleware('auth');

/* ------------------------------Employee Routes -----------------------------  */
// Route to the main panel
Route::get('/home', [EmployeeController::class, 'index'])->name('main-panel')->middleware('auth');
// Route show employee by id
Route::get('/get_employee/{id}', [EmployeeController::class, 'show'])->middleware('auth');
// Validate the form employee
Route::post('/validate_form', [EmployeeController::class, 'validatorForm'])->middleware('auth');
// Store employees for csv file
Route::post('/store_employees', [EmployeeController::class, 'storeEmployees'])->middleware('auth');
// Store the employee
Route::post('/store_employee', [EmployeeController::class, 'store'])->middleware('auth');
// Update the employee
Route::put('/update_employee', [EmployeeController::class, 'update'])->middleware('auth');
// Delete the employee
Route::delete('/delete_employee/{idEmployee}/{idUser}', [EmployeeController::class, 'destroy'])->middleware('auth');
// Show a list of employees according to established parameters.
Route::get('/filter_employee', [EmployeeController::class, 'showByParameters'])->middleware('auth');
// Export employees to pdf file
Route::get('/export_employees', [EmployeeController::class, 'showByParameters'])->name('export_employees')->middleware('auth');

/* ------------------------------History Access ------------------------------- */

// Get histories access by employee id
Route::get('history/{id}', [HistoryAccessController::class, 'show'])->middleware('auth');