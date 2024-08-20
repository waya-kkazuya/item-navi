<?php

namespace App\Listeners;

use App\Events\RequestedItemDetect;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class RequestedItemDetectListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(RequestedItemDetect $event): void
    {
        //
    }
}
