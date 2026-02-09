<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('user.{id}', function ($user, $id) {
    \Log::info("Authorizing user {$user->id} for channel user.{$id}");
    return (int) $user->id === (int) $id;
});
