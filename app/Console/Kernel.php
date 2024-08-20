<?php

namespace App\Console;


use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Models\Inspection;
use App\Models\Disposal;
use App\Notifications\InspectionScheduleNotification;
use App\Notifications\DisposalScheduleNotification;
use Carbon\Carbon;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();
        $schedule->call(function () {
            $this->sendInspectionNotifications();
            $this->sendDisposalNotifications();
        })->weekdays()->at('08:00');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }

    
    protected function sendInspectionNotifications()
    {
        $inspections = Inspection::whereDate('scheduled_date', Carbon::today())->get();
        foreach ($inspections as $inspection) {
            $users = User::all();
            foreach ($users as $user) {
                $user->notify(new InspectionScheduleNotification($inspection));
            }
        }
    }

    protected function sendDisposalNotifications()
    {
        $disposals = Disposal::whereDate('scheduled_date', Carbon::today())->get();
        foreach ($disposals as $disposal) {
            $users = User::all();
            foreach ($users as $user) {
                $user->notify(new DisposalScheduleNotification($disposal));
            }
        }
    }    
}
