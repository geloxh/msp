<?php

use App\Modules\Tickets\Controllers\TicketController;

Route::middleware(['auth', 'permission:tickets.read'])->group(function () {
    Route::resource('tickets', TicketController::class);
    Route::post('tickets/{ticket}/assign/{user}', [TicketController::class, 'assign'])
        ->name('tickets.assign');
    Route::post('tickets/{ticket}/close', [TicketController::class, 'close'])
        ->name('tickets.close');
});
