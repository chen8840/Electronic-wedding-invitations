<?php

namespace App\Listeners;

use App\Events\SyncImagesEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SyncImageListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  SyncImagesEvent  $event
     * @return void
     */
    public function handle(SyncImagesEvent $event)
    {
        $event->user->getInvitation()->syncImages();
    }
}
