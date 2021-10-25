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
     * Create a new job instance.
     *
     * @param int $id
     * @return void
     */
    public function __construct($id)
    {
        $this->eventId = $id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        return Asterisk::autoSetPinForRequest($this->eventId);
    }
}
