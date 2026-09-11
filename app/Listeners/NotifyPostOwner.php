<?php

namespace App\Listeners;

use App\Events\CommentCreated;
use App\Mail\NewCommentNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class NotifyPostOwner implements ShouldQueue
{
    public $tries = 3;
    public $backoff = 10;
    public function handle(CommentCreated $event): void
    {
        $postOwner = $event->comment->post->user;

        Mail::to($postOwner->email)
            ->send(new NewCommentNotification($event->comment));
    }
    public function failed(CommentCreated $event, \Throwable $exception): void
    {
        Log::error("Failed to notify post owner about comment #{$event->comment->id}: {$exception->getMessage()}");
    }
}
