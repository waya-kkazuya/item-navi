<?php

namespace App\Console;


use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Models\Inspection;
use App\Models\Disposal;
use App\Notifications\InspectionScheduleNotification;
use App\Notifications\DisposalScheduleNotification;
use Carbon\Carbon;
use App\Http\Controllers\UpdateHtaccessForGitHubActionsController;


class Kernel extends ConsoleKernel
{
    protected $commands = [
        Commands\InspectionSchedule::class,
        Commands\DisposalSchedule::class,
    ];
    
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('app:inspection-schedule')->weekdays()->at('00:00');
        $schedule->command('app:disposal-schedule')->weekdays()->at('00:00');

        // GitHubのAPIからGitHub Actionsで使われるIPアドレスを取得する
        // $schedule->call(function () {
        //     // 権限を持つユーザーでログイン
        //     $user = User::where('role', '1')->first();
        //     Auth::login($user);
        //     $controller = new UpdateGitHubActionsHtaccessController();
        //     $controller->update();
        //     Auth::logout();
        // })->daily();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
