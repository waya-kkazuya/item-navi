<?php

namespace App\Console\Commands;

use App\Models\Inspection;
use App\Models\User;
use App\Notifications\InspectionScheduleNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;

class InspectionSchedule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:inspection-schedule';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dates = [
            Carbon::today()->addWeeks(4)->toDateString(),
            Carbon::today()->addWeeks(2)->toDateString(),
            Carbon::today()->addWeek()->toDateString(),
            Carbon::today()->addDays(3)->toDateString(),
            Carbon::today()->addDay()->toDateString(),
            Carbon::today()->toDateString(),
        ];

        $inspections = Inspection::whereIn('inspection_scheduled_date', $dates)->get();

        \Log::info('sendInspectionNotifications called'); // ログを記録

        foreach ($inspections as $inspection) {
            $users = User::whereIn('role', [0, 1, 5])->get(); // roleが1（admin）または5（staff）のユーザーを取得
            foreach ($users as $user) {
                $user->notify(new InspectionScheduleNotification($inspection));
            }
        }
    }
}
