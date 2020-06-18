<?php

namespace App\Events;

use App\Models\Task;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TaskCancelled implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    protected $task;
    protected $companyId;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Task $task, $companyId)
    {
        $this->task = $task;
        $this->companyId = $companyId;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\PrivateChannel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel("company.{$this->companyId}.info");
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'cancelled';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'id' => $this->task->id,
            'person_name' => $this->task->person_name,
            'cancellation' => [
                'reason' => $this->task->cancellation->reason,
                'created_at' => $this->task->cancellation->created_at->format('Y-m-d'),
            ]
        ];
    }
}
