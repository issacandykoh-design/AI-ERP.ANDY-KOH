<?php

namespace App\Observers;

use App\Events\InvitationEmailEvent;
use App\Models\UserInvitation;

class UserInvitationObserver
{

    public function created(UserInvitation $invite)
    {
        if (!isRunningInConsoleOrSeeding()) {
            if ($invite->invitation_type == 'email') {
                event(new InvitationEmailEvent($invite));
            }
        }
    }

    public function creating(UserInvitation $model)
    {
        if (function_exists('company') && company()) {
            $model->company_id = company()->id;
        } elseif ($model->user) {
            $model->company_id = $model->user->company_id;
        }
    }

}
