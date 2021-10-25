<?php

namespace App\Jobs;

use App\Http\Controllers\Call\Asterisk;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AsteriskIncomingCallStartJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Идентификатор события
     * 
     * @var int
     */
    public $eventId;

    /**
     * Данные входящего события
     * 
     * @var array
     */
    public $eventData;

    /**
     * Create a new job instance.
     *
     * @param int $id
     * @param array $data
     * @return void
     */
    public function __construct($id, $data)
    {
        $this->eventId = $id;
        $this->eventData = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        return Asterisk::autoSetPinForRequest($this->eventId, $this->eventData);
    }
}
