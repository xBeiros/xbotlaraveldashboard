<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('discord.login'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// Rechtliche Seiten (DSGVO, Impressum, Cookies, ToS) – öffentlich, URLs auf Englisch
Route::get('/privacy', fn () => Inertia::render('Legal/Datenschutz'))->name('legal.privacy');
Route::get('/imprint', fn () => Inertia::render('Legal/Impressum'))->name('legal.imprint');
Route::get('/cookies', fn () => Inertia::render('Legal/Cookies'))->name('legal.cookies');
Route::get('/terms', fn () => Inertia::render('Legal/Nutzungsbedingungen'))->name('legal.terms');

// Discord Linked Roles Verification – Redirect zur Anmeldung (für Discord Developer Portal URL)
Route::get('/auth/discord/verify', fn () => redirect()->route('discord.login'))->name('discord.linked-roles-verify');

    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])
        ->middleware(['auth'])
        ->name('dashboard');
    
    // Dashboard Widgets
    Route::post('/dashboard/widgets', [\App\Http\Controllers\DashboardController::class, 'storeWidget'])
        ->name('dashboard.widgets.store');
    Route::put('/dashboard/widgets/{id}', [\App\Http\Controllers\DashboardController::class, 'updateWidget'])
        ->name('dashboard.widgets.update');
    Route::delete('/dashboard/widgets/{id}', [\App\Http\Controllers\DashboardController::class, 'deleteWidget'])
        ->name('dashboard.widgets.delete');
    Route::post('/dashboard/widgets/reorder', [\App\Http\Controllers\DashboardController::class, 'reorderWidgets'])
        ->name('dashboard.widgets.reorder');
    Route::get('/dashboard/widgets/data/{type}', [\App\Http\Controllers\DashboardController::class, 'getWidgetData'])
        ->name('dashboard.widgets.data');

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
    Route::post('/guild/{guild}/addon/toggle', [\App\Http\Controllers\GuildConfigController::class, 'toggleAddOn'])
        ->name('guild.addon.toggle');
    
    // Team-Verwaltung
    Route::get('/guild/{guild}/team-management', [\App\Http\Controllers\DashboardController::class, 'teamManagement'])
        ->name('guild.team-management');
    Route::post('/guild/{guild}/team-management/ranks', [\App\Http\Controllers\GuildConfigController::class, 'storeTeamRank'])
        ->name('guild.team-management.rank.store');
    Route::put('/guild/{guild}/team-management/ranks/{id}', [\App\Http\Controllers\GuildConfigController::class, 'updateTeamRank'])
        ->name('guild.team-management.rank.update');
    Route::delete('/guild/{guild}/team-management/ranks/{id}', [\App\Http\Controllers\GuildConfigController::class, 'deleteTeamRank'])
        ->name('guild.team-management.rank.delete');
    Route::put('/guild/{guild}/team-management/ranks/{id}/toggle', [\App\Http\Controllers\GuildConfigController::class, 'toggleTeamRankVisibility'])
        ->name('guild.team-management.rank.toggle');
    Route::post('/guild/{guild}/team-management/ranks/{id}/move', [\App\Http\Controllers\GuildConfigController::class, 'moveTeamRank'])
        ->name('guild.team-management.rank.move');
    Route::post('/guild/{guild}/team-management/members', [\App\Http\Controllers\GuildConfigController::class, 'storeTeamMember'])
        ->name('guild.team-management.member.store');
    Route::delete('/guild/{guild}/team-management/members/{id}', [\App\Http\Controllers\GuildConfigController::class, 'deleteTeamMember'])
        ->name('guild.team-management.member.delete');
    Route::put('/guild/{guild}/team-management/config', [\App\Http\Controllers\GuildConfigController::class, 'updateTeamManagementConfig'])
        ->name('guild.team-management.config.update');
    Route::post('/guild/{guild}/team-management/members/{id}/remove-rights', [\App\Http\Controllers\GuildConfigController::class, 'removeTeamRights'])
        ->name('guild.team-management.member.remove-rights');
    Route::post('/guild/{guild}/team-management/members/{id}/add-role', [\App\Http\Controllers\GuildConfigController::class, 'addTeamMemberRole'])
        ->name('guild.team-management.member.add-role');
    Route::post('/guild/{guild}/team-management/templates', [\App\Http\Controllers\GuildConfigController::class, 'storeTeamAnnouncementTemplate'])
        ->name('guild.team-management.template.store');
    Route::put('/guild/{guild}/team-management/templates/{id}', [\App\Http\Controllers\GuildConfigController::class, 'updateTeamAnnouncementTemplate'])
        ->name('guild.team-management.template.update');
    Route::delete('/guild/{guild}/team-management/templates/{id}', [\App\Http\Controllers\GuildConfigController::class, 'deleteTeamAnnouncementTemplate'])
        ->name('guild.team-management.template.delete');
    
    // Team-Verwaltung
    Route::get('/guild/{guild}/team-management', [\App\Http\Controllers\DashboardController::class, 'teamManagement'])
        ->name('guild.team-management');
    Route::get('/guild/{guild}/faction-management', [\App\Http\Controllers\DashboardController::class, 'factionManagement'])
        ->name('guild.faction-management');
    Route::put('/guild/{guild}/faction-management/config', [\App\Http\Controllers\GuildConfigController::class, 'updateFactionManagementConfig'])
        ->name('guild.faction-management.config.update');
    Route::get('/guild/{guild}/welcome', [\App\Http\Controllers\DashboardController::class, 'welcome'])
        ->name('guild.welcome');
    Route::get('/guild/{guild}/embed-sender', [\App\Http\Controllers\DashboardController::class, 'embedSender'])
        ->name('guild.embed-sender');
    Route::post('/guild/{guild}/saved-embeds', [\App\Http\Controllers\GuildConfigController::class, 'storeSavedEmbed'])
        ->name('guild.saved-embeds.store');
    Route::put('/guild/{guild}/saved-embeds/{id}', [\App\Http\Controllers\GuildConfigController::class, 'updateSavedEmbed'])
        ->name('guild.saved-embeds.update');
    Route::delete('/guild/{guild}/saved-embeds/{id}', [\App\Http\Controllers\GuildConfigController::class, 'deleteSavedEmbed'])
        ->name('guild.saved-embeds.delete');
    Route::post('/guild/{guild}/embed-sender/send', [\App\Http\Controllers\GuildConfigController::class, 'sendSavedEmbed'])
        ->name('guild.embed-sender.send');
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
    Route::get('/guild/{guild}/statistics-channel', [\App\Http\Controllers\DashboardController::class, 'statisticsChannel'])
        ->name('guild.statistics-channel');
    Route::put('/guild/{guild}/statistics-channel', [\App\Http\Controllers\GuildConfigController::class, 'updateStatisticsChannel'])
        ->name('guild.statistics-channel.update');

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
    Route::delete('/guild/{guild}/tickets/{ticketId}/transcript', [\App\Http\Controllers\GuildConfigController::class, 'deleteTicketTranscript'])
        ->name('guild.ticket.transcript.delete');
    Route::post('/guild/{guild}/tickets/transcripts/delete-multiple', [\App\Http\Controllers\GuildConfigController::class, 'deleteMultipleTicketTranscripts'])
        ->name('guild.ticket.transcript.deleteMultiple');
    Route::put('/guild/{guild}/ticket-transcript-setting', [\App\Http\Controllers\GuildConfigController::class, 'updateTicketTranscriptSetting'])
        ->name('guild.ticket-transcript-setting.update');
    Route::put('/guild/{guild}/ticket-close-config', [\App\Http\Controllers\GuildConfigController::class, 'updateTicketCloseConfig'])
        ->name('guild.ticket-close-config.update');
    
    // Giveaway
    Route::get('/guild/{guild}/giveaway', [\App\Http\Controllers\DashboardController::class, 'giveaway'])
        ->name('guild.giveaway');
    Route::post('/guild/{guild}/giveaway', [\App\Http\Controllers\GuildConfigController::class, 'storeGiveaway'])
        ->name('guild.giveaway.store');
    Route::delete('/guild/{guild}/giveaway/{id}', [\App\Http\Controllers\GuildConfigController::class, 'deleteGiveaway'])
        ->name('guild.giveaway.delete');
    
    // Birthdays
    Route::get('/guild/{guild}/birthdays', [\App\Http\Controllers\DashboardController::class, 'birthdays'])
        ->name('guild.birthdays');
    Route::post('/guild/{guild}/birthdays', [\App\Http\Controllers\GuildConfigController::class, 'storeBirthday'])
        ->name('guild.birthdays.store');
    Route::put('/guild/{guild}/birthdays/{id}', [\App\Http\Controllers\GuildConfigController::class, 'updateBirthday'])
        ->name('guild.birthdays.update');
    Route::delete('/guild/{guild}/birthdays/{id}', [\App\Http\Controllers\GuildConfigController::class, 'deleteBirthday'])
        ->name('guild.birthdays.delete');
    Route::put('/guild/{guild}/birthday-config', [\App\Http\Controllers\GuildConfigController::class, 'updateBirthdayConfig'])
        ->name('guild.birthday-config.update');
    
    // Birthdays
    Route::get('/guild/{guild}/birthdays', [\App\Http\Controllers\DashboardController::class, 'birthdays'])
        ->name('guild.birthdays');
    Route::post('/guild/{guild}/birthdays', [\App\Http\Controllers\GuildConfigController::class, 'storeBirthday'])
        ->name('guild.birthdays.store');
    Route::put('/guild/{guild}/birthdays/{id}', [\App\Http\Controllers\GuildConfigController::class, 'updateBirthday'])
        ->name('guild.birthdays.update');
    Route::delete('/guild/{guild}/birthdays/{id}', [\App\Http\Controllers\GuildConfigController::class, 'deleteBirthday'])
        ->name('guild.birthdays.delete');
    
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
