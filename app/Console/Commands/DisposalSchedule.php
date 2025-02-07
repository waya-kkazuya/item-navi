<?php

namespace App\Console\Commands;

use App\Models\Disposal;
use App\Models\User;
use App\Notifications\DisposalScheduleNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DisposalSchedule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:disposal-schedule';

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
            Carbon::today()->addWeeks(4),
            Carbon::today()->addWeeks(2),
            Carbon::today()->addWeek(),
            Carbon::today()->addDays(3),
            Carbon::today()->addDay(),
            Carbon::today(),
        ];

        $disposals = Disposal::whereIn('disposal_scheduled_date', $dates)->get();

        \Log::info('sendDisposalNotifications called');

        foreach ($disposals as $disposal) {
            $users = User::whereIn('role', [1, 5])->get(); // roleが1（admin）または5（staff）のユーザーを取得
            foreach ($users as $user) {
                $user->notify(new DisposalScheduleNotification($disposal));
            }
        }
    }
}
