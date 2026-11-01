<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CmdbController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GuideController;
use App\Http\Controllers\KnowledgeController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\OpsController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

Route::get('/', LandingController::class)->name('landing');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
});
Route::post('/coba-sebagai-tamu', [AuthController::class, 'guest'])->name('guest.login');
Route::get('/coba-sebagai-tamu', [AuthController::class, 'guest'])->name('guest.link');
Route::get('/panduan', [GuideController::class, 'index'])->name('guide.index');
Route::get('/panduan/{key}', [GuideController::class, 'panel'])->name('guide.panel');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth', 'demo.org'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/scope', [DashboardController::class, 'scope'])->name('scope');

    Route::get('/tickets', [TicketController::class, 'index'])->name('tickets.index');
    Route::get('/tickets/create', [TicketController::class, 'create'])->name('tickets.create');
    Route::post('/tickets', [TicketController::class, 'store'])->name('tickets.store');
    Route::get('/tickets/{ticket:ref}', [TicketController::class, 'show'])->name('tickets.show');
    Route::post('/tickets/{ticket:ref}/transition', [TicketController::class, 'transition'])->name('tickets.transition');
    Route::post('/tickets/{ticket:ref}/comment', [TicketController::class, 'comment'])->name('tickets.comment');
    Route::get('/changes/calendar', [TicketController::class, 'calendar'])->name('changes.calendar');

    Route::get('/cmdb', [CmdbController::class, 'index'])->name('cis.index');
    Route::get('/cmdb/{ci}', [CmdbController::class, 'show'])->name('cis.show');
    Route::get('/topology', [CmdbController::class, 'topology'])->name('topology.index');
    Route::get('/topology/data', [CmdbController::class, 'topologyData'])->name('topology.data');
    Route::get('/locations', [CmdbController::class, 'locations'])->name('locations.index');
    Route::get('/brands', [CmdbController::class, 'brands'])->name('brands.index');

    Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
    Route::get('/services/{service}', [ServiceController::class, 'show'])->name('services.show');
    Route::get('/slas', [ServiceController::class, 'slas'])->name('slas.index');
    Route::get('/contracts', [ServiceController::class, 'contracts'])->name('contracts.index');

    Route::get('/knowledge', [KnowledgeController::class, 'index'])->name('knowledge.index');
    Route::get('/knowledge/faq/{faq}', [KnowledgeController::class, 'faq'])->name('knowledge.faq');
    Route::get('/knowledge/known-error/{knownError}', [KnowledgeController::class, 'knownError'])->name('knowledge.ke');

    Route::get('/contacts', [ContactController::class, 'index'])->name('contacts.index');
    Route::get('/organizations', [ContactController::class, 'organizations'])->name('organizations.index');

    Route::get('/events', [OpsController::class, 'events'])->name('events.index');
    Route::get('/improvements', [OpsController::class, 'improvements'])->name('improvements.index');
    Route::get('/itil4', [OpsController::class, 'itil'])->name('itil.index');
    Route::get('/reports', [OpsController::class, 'reports'])->name('reports.index');
});
