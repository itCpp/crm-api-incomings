<?php

namespace App\Console\Commands;

use App\Http\Controllers\Incomings;
use App\Models\Old\CallDetailRecords;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class AudioCheckRecordsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'audio:checkrecords';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Перепроверяет длительность аудиозаписей разговоров для cr01 и cr02';

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
        if (!$this->host = env('CALL_RECORDS_SERVER', null))
            return 0;

        $this->count_file = 1;

        CallDetailRecords::where(function ($query) {
            $query->where('path', 'LIKE', "%cr01%")
                ->orWhere('path', 'LIKE', "%cr02%");
        })
            ->chunk(50, function ($rows) {

                foreach ($rows as $row) {

                    // $command = "ssh root@192.168.0.11 find /var/www/html/vois/2022/04/28 -regex '.*cr0.*'";
                    // exec($command, $output);
                    // dd($output);
                    // dd($row->toArray());

                    $duration = $this->updateDurationAudioFile($row);

                    $row->duration = $duration ? round($duration, 0) : null;
                    $row->save();

                    $this->count_file++;
                }
            });

        return 0;
    }

    /**
     * Обновляет данные файла
     * 
     * @param  \App\Models\Old\CallDetailRecords $row
     * @return int|null
     */
    public function updateDurationAudioFile($row)
    {
        $path = $this->host . $row->path;

        $duration = null;
        $duration_old = (int) $row->duration;

        $count = Str::of($this->count_file)->padLeft(4, '0');
        $this->info("<bg=blue;fg=white>[{$count}]</> {$path}");

        try {
            $duration = Incomings::getDurationFile($path);
            $duration_new = (int) $duration;

            $color = ($duration_new == $duration_old) ? "white" : "green";

            $this->line("Change duration <fg={$color};options=bold>{$duration_old}</> >>> <fg={$color};options=bold>{$duration_new}</>");
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }

        return $duration;
    }
}
