<?php

namespace App\Http\Controllers\Telegram\Jobs;

use App\Http\Controllers\Telegram\Callbacks\Handler;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CallbackQueryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Идентификатор пользователя
     * 
     * @var string|int
     */
    public $chat_id;

    /**
     * Текст комманды
     * 
     * @var string
     */
    public $command;

    /**
     * Create a new job instance.
     *
     * @param string|int $chat_id
     * @param string $command
     * @return void
     */
    public function __construct($chat_id, $command)
    {
        $this->chat_id = $chat_id;
        $this->command = $command;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $callback = new Handler($this->chat_id, $this->command);

        if ($callback->command)
            $callback->command->handle();
    }
}
