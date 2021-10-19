<?php

namespace App\Jobs;

use App\Http\Controllers\Incomings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateDurationTime implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Экземпляр можеи информации о файле
     * 
     * @var \App\Models\Old\CallDetailRecords
     */
    public $row;

    /**
     * Create a new job instance.
     *
     * @param \App\Models\Old\CallDetailRecords $row
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
        Incomings::updateDurationTime($this->row);
    }
}
