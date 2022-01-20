<?php

namespace App\Http\Controllers\Telegram\Jobs;

use App\Http\Controllers\Telegram\Telegram;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ReplyMessagesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Список чат-групп
     * 
     * @var array
     */
    protected $chats;

    /**
     * Create a new job instance.
     *
     * @param array[]
     * @return void
     */
    public function __construct(...$chats)
    {
        $this->chats = $chats;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $telegram = new Telegram;

        foreach ($this->chats as $data) {
            $data['disable_notification'] = true;
            $data['protect_content'] = true;

            $telegram->forwardMessage($data);
        }
    }
}
