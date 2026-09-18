<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Models\User;
use Illuminate\Notifications\Messages\BroadcastMessage;

class UserFollowed extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public User $follower) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'follower_id' => $this->follower->id,
            'name' => $this->follower->name,
            'avatar'      => $this->follower->avatar_url ?? null,
            'message'     => "{$this->follower->name} started following you.",
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'follower_id' => $this->follower->id,
            'name' => $this->follower->name,
            'avatar'      => $this->follower->avatar_url ?? null,
            'message'     => "{$this->follower->name} started following you.",
        ]);
    }
}
