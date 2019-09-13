<?php

use App\Http\Controllers\Backend\DashboardController;

// All route names are prefixed with 'admin.'.
Route::redirect('/', '/admin/dashboard', 301);
Route::group(['prefix' => 'dashboard'], function() {
  Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
  Route::get('/{any}', [DashboardController::class, 'index'])->where('any', '.*');
});
