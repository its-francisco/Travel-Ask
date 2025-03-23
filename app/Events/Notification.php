<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;


// to run it: event(new Notification('hello world', 1, 'vote', 1));

class Notification implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $message;
    public $question_id;
    public $theme;
    public $user_id;

    public function __construct($message, $question_id, $theme, $user_id)
    {
        $this->question_id = $question_id;
        $this->message = $message;
        $this->theme = $theme;
        $this->user_id = $user_id;
    }


    public function broadcastOn(){
        return 'user.' . $this->user_id;
    }

    public function broadcastAs() {
        return 'notification';
    }

}
