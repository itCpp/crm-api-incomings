<?php

namespace App\Console\Commands;

use App\Models\Old\CallDetailRecords;
use Illuminate\Console\Command;

class GetDurationCallAudioFile extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calls:checkduration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checked duration audio call records';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        set_time_limit(0);
        $host = env('CALL_RECORDS_SERVER', null);

        if (!$host) {
            echo "Адрес сервера с файлами не определен в конфиге\r\n";
            return 0;
        }

        $file = CallDetailRecords::where('duration', 0)->first();

        $cmd = "ffmpeg -i " . $host . $file->path;
        $response = exec($cmd);

        dd($response);

        return 0;
    }
}
