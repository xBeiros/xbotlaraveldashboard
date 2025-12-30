<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('discord.login'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Bot Einladung
    Route::get('/bot/invite', [\App\Http\Controllers\BotInviteController::class, 'invite'])->name('bot.invite');
    Route::get('/bot/callback', [\App\Http\Controllers\BotInviteController::class, 'callback'])->name('bot.callback');
    
    // Server Konfiguration
    Route::get('/guild/{guild}/config', [\App\Http\Controllers\DashboardController::class, 'config'])
        ->name('guild.config');
    Route::get('/guild/{guild}/welcome', [\App\Http\Controllers\DashboardController::class, 'welcome'])
        ->name('guild.welcome');
    Route::get('/guild/{guild}/reaction-roles', [\App\Http\Controllers\DashboardController::class, 'reactionRoles'])
        ->name('guild.reaction-roles');
    Route::put('/guild/{guild}/welcome', [\App\Http\Controllers\GuildConfigController::class, 'updateWelcome'])
        ->name('guild.welcome.update');
    Route::put('/guild/{guild}/goodbye', [\App\Http\Controllers\GuildConfigController::class, 'updateGoodbye'])
        ->name('guild.goodbye.update');
    Route::get('/guild/{guild}/social', [\App\Http\Controllers\DashboardController::class, 'social'])
        ->name('guild.social');
    Route::post('/guild/{guild}/social', [\App\Http\Controllers\GuildConfigController::class, 'storeSocial'])
        ->name('guild.social.store');
    Route::put('/guild/{guild}/social/{id}', [\App\Http\Controllers\GuildConfigController::class, 'updateSocial'])
        ->name('guild.social.update');
    Route::delete('/guild/{guild}/social/{id}', [\App\Http\Controllers\GuildConfigController::class, 'deleteSocial'])
        ->name('guild.social.delete');
    
    // Auto-Moderation
    Route::get('/guild/{guild}/auto-moderation', [\App\Http\Controllers\DashboardController::class, 'autoModeration'])
        ->name('guild.auto-moderation');
    Route::put('/guild/{guild}/auto-moderation', [\App\Http\Controllers\GuildConfigController::class, 'updateAutoModeration'])
        ->name('guild.auto-moderation.update');
    
    // Leveling
    Route::get('/guild/{guild}/leveling', [\App\Http\Controllers\DashboardController::class, 'leveling'])
        ->name('guild.leveling');
    Route::put('/guild/{guild}/leveling', [\App\Http\Controllers\GuildConfigController::class, 'updateLeveling'])
        ->name('guild.leveling.update');
    Route::post('/guild/{guild}/role-rewards', [\App\Http\Controllers\GuildConfigController::class, 'storeRoleReward'])
        ->name('guild.role-rewards.store');
    Route::delete('/guild/{guild}/role-rewards/{id}', [\App\Http\Controllers\GuildConfigController::class, 'deleteRoleReward'])
        ->name('guild.role-rewards.delete');
    Route::put('/guild/{guild}/rank-card', [\App\Http\Controllers\GuildConfigController::class, 'updateRankCard'])
        ->name('guild.rank-card.update');
    
    // Reaction Roles
    Route::post('/guild/{guild}/reaction-roles', [\App\Http\Controllers\GuildConfigController::class, 'storeReactionRole'])
        ->name('guild.reaction-roles.store');
    Route::put('/guild/{guild}/reaction-roles/{id}', [\App\Http\Controllers\GuildConfigController::class, 'updateReactionRole'])
        ->name('guild.reaction-roles.update');
    Route::delete('/guild/{guild}/reaction-roles/{id}', [\App\Http\Controllers\GuildConfigController::class, 'deleteReactionRole'])
        ->name('guild.reaction-roles.delete');
    Route::post('/guild/{guild}/reaction-roles/{id}/resend', [\App\Http\Controllers\GuildConfigController::class, 'resendReactionRole'])
        ->name('guild.reaction-roles.resend');
    
    // Server-Verwaltung
    Route::get('/guild/{guild}/server-management', [\App\Http\Controllers\DashboardController::class, 'serverManagement'])
        ->name('guild.server-management');
    Route::put('/guild/{guild}/server-management', [\App\Http\Controllers\GuildConfigController::class, 'updateServerManagement'])
        ->name('guild.server-management.update');
    
    // Nachrichten Löschen
    Route::get('/guild/{guild}/delete-messages', [\App\Http\Controllers\DashboardController::class, 'deleteMessages'])
        ->name('guild.delete-messages');
    Route::post('/guild/{guild}/delete-messages', [\App\Http\Controllers\GuildConfigController::class, 'deleteMessages'])
        ->name('guild.delete-messages.post');
    
    // Ticket System
    Route::get('/guild/{guild}/ticket-system', [\App\Http\Controllers\DashboardController::class, 'ticketSystem'])
        ->name('guild.ticket-system');
    Route::post('/guild/{guild}/ticket-post', [\App\Http\Controllers\GuildConfigController::class, 'storeTicketPost'])
        ->name('guild.ticket-post.store');
    Route::post('/guild/{guild}/ticket-post/resend', [\App\Http\Controllers\GuildConfigController::class, 'resendTicketPost'])
        ->name('guild.ticket-post.resend');
    Route::post('/guild/{guild}/ticket-categories', [\App\Http\Controllers\GuildConfigController::class, 'storeTicketCategory'])
        ->name('guild.ticket-categories.store');
    Route::put('/guild/{guild}/ticket-categories/{id}', [\App\Http\Controllers\GuildConfigController::class, 'updateTicketCategory'])
        ->name('guild.ticket-categories.update');
    Route::delete('/guild/{guild}/ticket-categories/{id}', [\App\Http\Controllers\GuildConfigController::class, 'deleteTicketCategory'])
        ->name('guild.ticket-categories.delete');
    Route::get('/guild/{guild}/tickets/{ticketId}/transcript', [\App\Http\Controllers\GuildConfigController::class, 'getTicketTranscript'])
        ->name('guild.ticket.transcript');
    Route::put('/guild/{guild}/ticket-transcript-setting', [\App\Http\Controllers\GuildConfigController::class, 'updateTicketTranscriptSetting'])
        ->name('guild.ticket-transcript-setting.update');
    Route::put('/guild/{guild}/ticket-close-config', [\App\Http\Controllers\GuildConfigController::class, 'updateTicketCloseConfig'])
        ->name('guild.ticket-close-config.update');
    
    // Auto-Delete Messages
    Route::post('/guild/{guild}/auto-delete-messages', [\App\Http\Controllers\GuildConfigController::class, 'storeAutoDeleteMessage'])
        ->name('guild.auto-delete-messages.store');
    Route::put('/guild/{guild}/auto-delete-messages/{id}', [\App\Http\Controllers\GuildConfigController::class, 'updateAutoDeleteMessage'])
        ->name('guild.auto-delete-messages.update');
    Route::put('/guild/{guild}/auto-delete-messages/{id}/toggle', [\App\Http\Controllers\GuildConfigController::class, 'toggleAutoDeleteMessage'])
        ->name('guild.auto-delete-messages.toggle');
    Route::delete('/guild/{guild}/auto-delete-messages/{id}', [\App\Http\Controllers\GuildConfigController::class, 'deleteAutoDeleteMessage'])
        ->name('guild.auto-delete-messages.delete');
});

require __DIR__.'/auth.php';
