<?php

use Illuminate\Support\Facades\Route;
use Rithy\ZktecoAdms\Http\Controllers\IclockController;
use Rithy\ZktecoAdms\Http\Controllers\ZkDeviceController;

// Strictly for ZKTeco devices, do not change the URI.
Route::get('/iclock/cdata', [IclockController::class, 'handshake']);
Route::post('/iclock/cdata', [IclockController::class, 'receiveRecords']);
Route::get('/iclock/getrequest', [IclockController::class, 'getrequest']);
Route::post('/iclock/devicecmd', [IclockController::class, 'deviceCmd']);
Route::get('/iclock/devicecmd', [IclockController::class, 'deviceCmd']);

Route::prefix('zk-devices')->group(function () {
    Route::get('/', [ZkDeviceController::class, 'index'])->name('zk-devices.index');
    Route::post('/{device}/sync-users', [ZkDeviceController::class, 'syncUsers'])->name('zk-devices.sync-users');
    Route::post('/{device}/sync-attendance', [ZkDeviceController::class, 'syncAttendance'])->name('zk-devices.sync-attendance');
    Route::post('/{device}/sync-biodata', [ZkDeviceController::class, 'syncBiodata'])->name('zk-devices.sync-biodata');
});
