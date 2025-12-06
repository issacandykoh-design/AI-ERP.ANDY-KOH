<?php

namespace App\Listeners;

use App\Events\InvitationEmailEvent;
use App\Notifications\InvitationEmail;
use Illuminate\Support\Facades\Notification;

class InvitationEmailListener
{

    /**
     * @param InvitationEmailEvent $event
     */

    public function handle(InvitationEmailEvent $event)
    {
        try {
            Notification::send($event->invite, new InvitationEmail($event->invite));
        } catch (\Throwable $e) {
            report($e);
        }
    }

}
