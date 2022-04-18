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
     * Идентификатор для отправки в старую ЦРМ
     * 
     * @var bool
     */
    public $to_old = false;

    /**
     * Create a new job instance.
     *
     * @param  \App\Models\IncomingEvent
     * @param  bool $to_old
     * @return void
     */
    public function __construct($row, $to_old = false)
    {
        $this->row = $row;
        $this->to_old = $to_old;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $mango = new Mango($this->to_old);
        $mango->event($this->row);
    }
}
