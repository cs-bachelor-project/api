<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('task.of.{id}', function ($user, $userId) {
    return $user->id == $userId;
});

Broadcast::channel('company.{id}.info', function ($user, $companyId) {
    if ($user->hasAnyRole(['admin', 'manager'])) {
        return $user->company_id == $companyId;
    }

    return false;
});

Broadcast::channel('company.{id}.msg', function ($user, $companyId) {
    if ($user->hasAnyRole(['driver'])) {
        return $user->company_id == $companyId;
    }

    return false;
});
