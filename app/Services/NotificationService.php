<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;

class NotificationService
{
    public function send(User $user, string $type, string $title, string $message, ?string $url = null): Notification
    {
        return Notification::create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'url' => $url,
        ]);
    }

    /** @param iterable<User> $users */
    public function sendMany(iterable $users, string $type, string $title, string $message, ?string $url = null): void
    {
        foreach ($users as $user) {
            $this->send($user, $type, $title, $message, $url);
        }
    }

    public function bkUsers()
    {
        $roles = \Spatie\Permission\Models\Role::whereIn('name', ['bk', 'super_admin'])->pluck('name')->all();

        return $roles === [] ? collect() : User::role($roles)->get();
    }
}
