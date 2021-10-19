<?php

namespace App\Console\Commands;

use App\Models\Old\CallDetailRecords;
use FFMpeg\FFMpeg;
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
     * Адрес сервера хранилища файлов
     * 
     * @var string|null
     */
    protected $host;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->host = env('CALL_RECORDS_SERVER', null);
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        set_time_limit(0);
        $start = microtime(true);

        if (!$this->host) {
            echo "Адрес сервера с файлами не определен в конфиге\r\n";
            return 0;
        }
        
        $file = true;

        while ($file) {
            if ($file = CallDetailRecords::where('duration', 0)->whereNotNull('duration')->first())
                $this->handleStep($file);
        }

        echo "Время выполнения скрипта: " . round(microtime(true) - $start, 4) . " сек.\r\n";

        return 0;
    }

    /**
     * Проверка одного файла
     * 
     * @param \App\Models\Old\CallDetailRecords $file
     * @return null
     */
    public function handleStep(CallDetailRecords $file)
    {
        $path = $this->host . $file->path;

        $ffmpeg = FFMpeg::create();
        $audio = $ffmpeg->open($path);

        $duration = $audio->getFormat()->get('duration');

        echo "[" . date("Y-m-d H:i:s") . "]\r\n";
        echo "FILE {$file->path}\n";
        echo "DURATION {$duration}\n";
        echo "Обновлено\r\n\n";

        $duration = (int) $duration;
        $file->duration = $duration ? round($duration, 0) : null;
        $file->save();

        return null;
    }
}
