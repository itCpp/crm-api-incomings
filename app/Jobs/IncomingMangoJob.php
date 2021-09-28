<?php

namespace App\Jobs;

use App\Http\Controllers\Call\Mango;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class IncomingMangoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Модель основновного события
     * 
     * @var \App\Models\IncomingEvent
     */
    public $row;

    /**
     * Create a new job instance.
     *
     * @param \App\Models\IncomingEvent
     * @return void
     */
    public function __construct($row)
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
        $mango = new Mango;
        $mango->event($this->row);
    }
}
