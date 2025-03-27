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
Route::post('/login', [UserController::class, 'login'])->name('login.action');
// Route to the logout action
Route::post('/logout', [UserController::class, 'logout'])->middleware('auth')->name('logout');
// Enable / Disable User
Route::put('/changeActive/{idUser}', [UserController::class, 'enableOrDisable'])->middleware('auth')->name('user.changeActive');

/* ------------------------------Employee Routes -----------------------------  */
// Route to the main panel
Route::get('/home', [EmployeeController::class, 'index'])->middleware('auth')->name('home');
// Route show employee by id
Route::get('/get_employee/{id}', [EmployeeController::class, 'show'])->middleware('auth')->name('employee.show');
// Validate the form employee
Route::post('/validate_form', [EmployeeController::class, 'validatorForm'])->middleware('auth')->name('employee.validate_form');
// Store employees for csv file
Route::post('/store_employees', [EmployeeController::class, 'storeEmployees'])->middleware('auth')->name('employee.store_employees');
// Store the employee
Route::post('/store_employee', [EmployeeController::class, 'store'])->middleware('auth')->name('employee.store');
// Update the employee
Route::put('/update_employee', [EmployeeController::class, 'update'])->middleware('auth')->name('employee.update');
// Delete the employee
Route::delete('/delete_employee/{idEmployee}/{idUser}', [EmployeeController::class, 'destroy'])->middleware('auth')->name('employee.delete');
// Show a list of employees according to established parameters.
Route::get('/filter_employee', [EmployeeController::class, 'showByParameters'])->middleware('auth')->name('employee.filter_employee');
// Export employees to pdf file
Route::get('/export_employees', [EmployeeController::class, 'showByParameters'])->middleware('auth')->name('employee.export_employees');

/* ------------------------------History Access ------------------------------- */

// Get histories access by employee id
Route::get('history/{id}', [HistoryAccessController::class, 'show'])->middleware('auth')->name('history.show');