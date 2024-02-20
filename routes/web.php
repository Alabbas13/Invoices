<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\InvoiceAchiveController;
use App\Http\Controllers\InvoicesAttachmentsController;
use App\Http\Controllers\InvoicesController;
use App\Http\Controllers\InvoicesDetailsController;
use App\Http\Controllers\InvoicesReportController;
use App\Http\Controllers\CustomerssReportController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\SectionsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use Illuminate\Support\Facades\Auth;
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

Route::get('/', function () {
    return view('auth.login');
});
//Route::get('/{page}', 'AdminController@index');


Auth::routes();
//Auth::routes(['register'=> false]);
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::resource('invoices', InvoicesController::class);

Route::resource('sections', SectionsController::class);

Route::resource('products', ProductsController::class);

Route::resource('InvoiceAttachments', InvoicesAttachmentsController::class);

Route::get('paid_invoices', [InvoicesController::class, 'paid_invoices']);

Route::get('unpaid_invoices', [InvoicesController::class, 'unpaid_invoices']);

Route::get('partial_invoices', [InvoicesController::class, 'partial_invoices']);

Route::resource('InvoiceAchive', InvoiceAchiveController::class);

Route::get('section/{id}', [InvoicesController::class, 'getproducts']);

Route::get('InvoicesDetails/{id}', [InvoicesDetailsController::class, 'index']);

Route::get('View_file/{invoice_number}/{file_name}', [InvoicesDetailsController::class, 'openFile']);

Route::get('download/{invoice_number}/{file_name}', [InvoicesDetailsController::class, 'getFile']);

Route::get('export_invoices', [InvoicesController::class, 'export'])->name('export_invoices');

Route::post('delete_file', [InvoicesDetailsController::class, 'destroy'])->name('delete_file');

Route::get('edit_invoice/{id}', [InvoicesController::class, 'edit']);

Route::get('Status_show/{id}', [InvoicesController::class, 'show'])->name('Status_show');

Route::post('Status_Update/{id}', [InvoicesController::class, 'Status_Update'])->name('Status_Update');

Route::get('print_invoice/{id}', [InvoicesController::class, 'print_invoice'])->name('print_invoice');

Route::group(['middleware' => ['auth']], function () {
    Route::resource('roles', RoleController::class);
    Route::resource('users', UserController::class);
});

Route::get('invoices_report', [InvoicesReportController::class,'index']);

Route::post('Search_invoices', [InvoicesReportController::class, 'Search_invoices']);

Route::get('customers_report', [CustomerssReportController::class,'index']);

Route::post('Search_customers', [CustomerssReportController::class,'Search_customers']);

Route::get('markAsAll',[InvoicesController::class,'markAll'])->name('markAsAll');

Route::get('/{page}', [AdminController::class, 'index']);
