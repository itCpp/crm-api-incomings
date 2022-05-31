<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class AsteriskRetryEventToCrmJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Модель файла
     * 
     * @var \App\Models\Old\CallDetailRecords
     */
    protected $file;

    /**
     * Create a new job instance.
     *
     * @param  \App\Models\Old\CallDetailRecords $file
     * @return void
     */
    public function __construct($file)
    {
        $this->file = $file;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $url = env('CRM_API_SERVER', 'http://localhost:8000') . "/api/calls/event";

        try {
            Http::withHeaders(['Accept' => 'application/json'])
                ->withOptions(['verify' => false])
                ->post($url . "?row_id={$this->file->id}", $this->file->toArray());
        } finally {
        }
    }
}
