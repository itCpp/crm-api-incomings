<?php

namespace App\Console\Commands;

use App\Http\Controllers\Incomings;
use App\Models\Old\CallDetailRecords;
use Illuminate\Console\Command;
use Symfony\Component\Console\Output\ConsoleOutput;

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

        $this->output = new ConsoleOutput;
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
            $this->output->writeln("\n<error>Адрес сервера с файлами не определен в конфиге</error>\n");
            return 0;
        }

        $file = true;
        // $id = 1316;

        while ($file) {

            $file = CallDetailRecords::where('duration', 0)->whereNotNull('duration')->first();
            // $file = CallDetailRecords::where('id', '>', $id)
            //     ->whereDate('created_at', "2021-10-19")
            //     ->whereNotNull('duration')
            //     ->first();

            if ($file instanceof CallDetailRecords) {
                $id = $file->id;

                try {
                    $updated = Incomings::updateDurationTime($file, $this->host);
                    $this->echoStep($updated);
                } catch (\FFMpeg\Exception\RuntimeException $e) {

                    $error = $e->getMessage();

                    $this->output->writeln([
                        "<fg=red>[" . date("Y-m-d H:i:s") . "]</>",
                        "<fg=red>{$this->host}{$file->path}</>",
                        "<error>{$error}</error>\n",
                    ]);
                }
            }
        }

        $stop = round(microtime(true) - $start, 2);
        $this->output->writeln("<question>Время выполнения скрипта: {$stop} сек</question>\n");

        return 0;
    }

    /**
     * Вывод информации в консоль
     * 
     * @param \App\Models\Old\CallDetailRecords $file
     * @return null
     */
    public function echoStep(CallDetailRecords $file)
    {
        $duration = $file->duration ?: "0";
        $color = $file->duration ? "green" : "cyan";

        $this->output->writeln([
            "<fg=green>[" . date("Y-m-d H:i:s") . "]</>",
            "<fg=green>{$this->host}{$file->path}</>",
            "<fg={$color}>DURATION {$duration} sec</>\n",
        ]);

        return null;
    }
}
