<?php

namespace App\Events;

use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TaskUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    protected $task;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Task $task)
    {
        $this->task = $task;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\PrivateChannel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel("task.of.{$this->task->user_id}");
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'updated';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        $with = [];

        foreach ($this->task->details as $detail) {
            array_push($with,             [
                'id' => $detail->id,
                'country' => $detail->country,
                'city' => $detail->city,
                'postal' => $detail->postal,
                'street' => $detail->street,
                'street_number' => $detail->street_number,
                'phone' => $detail->phone,
                'action' => $detail->action,
                'scheduled_at' => (new Carbon($detail->scheduled_at))->toDateTimeString(),
                'completed_at' => is_null($detail->completed_at) ? null : (new Carbon($detail->completed_at))->toDateTimeString(),
                'task' => [
                    'id' => $this->task->id,
                    'person_name' => $this->task->person_name,
                    'note' => $this->task->note,
                    'user_id' => $this->task->user_id,
                ]
            ]);
        }

        return $with;
    }
}
