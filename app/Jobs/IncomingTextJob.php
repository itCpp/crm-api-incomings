<?php

namespace App\Jobs;

use App\Models\IncomingTextRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class IncomingTextJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Экземпляр модели очереди
     * 
     * @var \App\Models\IncomingTextRequest
     */
    public $row;

    /**
     * Количество секунд, по истечении которых уникальная блокировка задания будет снята.
     *
     * @var int
     */
    public $uniqueFor = 5000;

    /**
     * Create a new job instance.
     *
     * @param \App\Models\IncomingTextRequest $row
     * @return void
     */
    public function __construct(IncomingTextRequest $row)
    {
        $this->row = $row;
    }

    /**
     * Уникальный идентификатор задания.
     *
     * @return string
     */
    public function uniqueId()
    {
        return $this->row->id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        return \App\Http\Controllers\Text\IncomingText::send($this->row);
    }
}
