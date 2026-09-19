<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\InvitationController;
use App\Http\Controllers\DefaultActivityController;
use App\Http\Controllers\MonthController;
use App\Http\Controllers\MonthFillController;
use App\Http\Controllers\MonthReportController;
use App\Http\Controllers\Settings\HolidayController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Settings\SecurityController;
use App\Http\Controllers\TimeEntryController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WeeklySlotController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/mes');

Route::middleware('guest')->controller(InvitationController::class)->group(function (): void {
    Route::get('/convite/{token}', 'show')->name('invitation.show');
    Route::post('/convite', 'store')->middleware('throttle:6,1')->name('invitation.store');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/mes/{year?}/{month?}', [MonthController::class, 'show'])
        ->where(['year' => '\d{4}', 'month' => '0?[1-9]|1[0-2]'])
        ->name('month.show');

    Route::middleware('throttle:20,1')
        ->where(['year' => '\d{4}', 'month' => '0?[1-9]|1[0-2]'])
        ->group(function (): void {
            Route::post('/mes/{year}/{month}/preencher', MonthFillController::class)->name('month.fill');
            Route::get('/mes/{year}/{month}/relatorio', MonthReportController::class)->name('month.report');
        });

    Route::controller(TimeEntryController::class)->group(function (): void {
        Route::post('/lancamentos', 'store')->name('time-entries.store');
        Route::put('/lancamentos/{timeEntry}', 'update')->can('update', 'timeEntry')->name('time-entries.update');
        Route::delete('/lancamentos/{timeEntry}', 'destroy')->can('delete', 'timeEntry')->name('time-entries.destroy');
    });

    Route::controller(WeeklySlotController::class)->group(function (): void {
        Route::get('/grade-semanal', 'index')->name('weekly-slots.index');
        Route::post('/grade-semanal', 'store')->name('weekly-slots.store');
        Route::put('/grade-semanal/{weeklySlot}', 'update')->can('update', 'weeklySlot')->name('weekly-slots.update');
        Route::delete('/grade-semanal/{weeklySlot}', 'destroy')->can('delete', 'weeklySlot')->name('weekly-slots.destroy');
    });

    Route::controller(DefaultActivityController::class)->group(function (): void {
        Route::get('/atividades-padrao', 'index')->name('default-activities.index');
        Route::post('/atividades-padrao', 'store')->name('default-activities.store');
        Route::put('/atividades-padrao/{defaultActivity}', 'update')->can('update', 'defaultActivity')->name('default-activities.update');
        Route::delete('/atividades-padrao/{defaultActivity}', 'destroy')->can('delete', 'defaultActivity')->name('default-activities.destroy');
    });

    Route::redirect('/configuracoes', '/configuracoes/perfil');
    Route::get('/configuracoes/perfil', [ProfileController::class, 'edit'])->name('profile.edit');

    Route::controller(HolidayController::class)->group(function (): void {
        Route::get('/configuracoes/feriados', 'index')->name('holidays.index');
        Route::post('/configuracoes/feriados', 'store')->name('holidays.store');
        Route::delete('/configuracoes/feriados/{holiday}', 'destroy')->can('delete', 'holiday')->name('holidays.destroy');
    });

    Route::controller(SecurityController::class)->group(function (): void {
        Route::get('/configuracoes/seguranca', 'edit')->name('security.edit');
        Route::delete('/configuracoes/conta', 'destroy')->middleware('throttle:5,1')->name('account.destroy');
    });

    Route::middleware('can:manage-users')->controller(UserController::class)->group(function (): void {
        Route::get('/usuarios', 'index')->name('users.index');
        Route::post('/usuarios', 'store')->middleware('throttle:20,1')->name('users.store');
        Route::post('/usuarios/{user}/convite', 'resendInvitation')->middleware('throttle:10,1')->name('users.resend');
        Route::put('/usuarios/{user}', 'update')->name('users.update');
    });
});
