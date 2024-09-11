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
        })->weekdays()->at('06:00');
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
        $dates = [
            Carbon::today()->addWeeks(4),
            Carbon::today()->addWeeks(2),
            Carbon::today()->addWeek(),
            Carbon::today()->addDays(3),
            Carbon::today()
        ];

        $inspections = Inspection::whereIn('scheduled_date', $dates)->get();
        
        foreach ($inspections as $inspection) {
            $users = User::whereIn('role', [1, 5])->get(); // roleが1（admin）または5（staff）のユーザーを取得
            foreach ($users as $user) {
                $user->notify(new InspectionScheduleNotification($inspection));
            }
        }
    }

    protected function sendDisposalNotifications()
    {
        $dates = [
            Carbon::today()->addWeeks(4),
            Carbon::today()->addWeeks(2),
            Carbon::today()->addWeek(),
            Carbon::today()->addDays(3),
            Carbon::today()
        ];

        $disposals = Disposal::whereIn('scheduled_date', $dates)->get();
        
        foreach ($disposals as $disposal) {
            $users = User::whereIn('role', [1, 5])->get(); // roleが1（admin）または5（staff）のユーザーを取得
            foreach ($users as $user) {
                $user->notify(new DisposalScheduleNotification($disposal));
            }
        }
    }    
}
