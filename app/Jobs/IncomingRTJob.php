<?php

namespace App\Jobs;

use App\Http\Controllers\Call\RT;
use App\Models\IncomingCallRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class IncomingRTJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Модель строки события
     * 
     * @var \App\Models\IncomingCallRequest
     */
    public $row;

    /**
     * Create a new job instance.
     *
     * @param \App\Models\IncomingCallRequest $row
     * @return void
     */
    public function __construct(IncomingCallRequest $row)
    {
        $this->row = $row;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        RT::newCall($this->row);
    }
}
