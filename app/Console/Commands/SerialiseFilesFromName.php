<?php

namespace App\Console\Commands;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Incomings;
use App\Models\Old\CallDetailRecords;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Symfony\Component\Console\Helper\ProgressBar;

class SerialiseFilesFromName extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calls:serialise';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Проверка неучтенных файлов записей разговоров';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->host = env('CALL_RECORDS_SERVER', null);

        $this->drop = [];
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        set_time_limit(0);

        $response = Http::get($this->host . "/files.php");
        $files = $response->json();

        if (!is_array($files) or !count($files)) {

            $this->newLine();
            $this->error(" Список файлов пуст ");
            $this->newLine();

            return 0;
        }

        $this->newLine();
        $this->info(" Обработка файлов ");
        $this->newLine();

        ProgressBar::setFormatDefinition('custom', ' %current%/%max% [%bar%] %message%');
        $bar = new ProgressBar($this->output, count($files));
        $bar->setFormat('custom');

        $bar->setMessage('Запуск скрипта');
        $bar->start();

        foreach ($files as $file) {

            $bar->setMessage($file);

            $parce = explode("-", $file);

            if (count($parce) == 4) {

                $phone = null;
                $type = null;

                if ($check = Controller::checkPhone($parce[0], 3)) {
                    $phone = $check;
                    $extension = $parce[1];
                    $type = "out";
                } elseif ($check = Controller::checkPhone($parce[1], 3)) {
                    $phone = $check;
                    $extension = $parce[0];
                    $type = "in";
                }

                if (!$phone) {
                    $this->drop[] = [
                        'count' => count($this->drop) + 1,
                        'name' => $file,
                        'message' => "<fg=red>Номер не определен</>",
                    ];
                    continue;
                }

                $datetime = explode(".", $parce[2]);
                $minuts = explode(".", $parce[3]);

                if (count($datetime) != 4 or count($minuts) != 2) {
                    $this->drop[] = [
                        'count' => count($this->drop) + 1,
                        'name' => $file,
                        'message' => "<fg=red>Неправильная дата</>",
                    ];
                    continue;
                }

                $date = "{$datetime[0]}-{$datetime[1]}-{$datetime[2]} {$datetime[3]}:{$minuts[0]}:" . rand(0, 59);

                $data = [
                    'phone' => $phone,
                    'extension' => $extension ?? null,
                    'path' => "/call/{$file}",
                    'call_at' => $date ?? null,
                    'type' => $type,
                    'duration' => 0,
                ];

                $this->createAndCheckAudio($data);
            } else {
                $this->drop[] = [
                    'count' => count($this->drop) + 1,
                    'name' => $file,
                    'message' => "<fg=red>Ошибка в наименовании</>",
                ];
            }

            $bar->advance();

            usleep(300);
        }

        $bar->setMessage("");
        $bar->finish();

        $this->newLine();

        if (count($this->drop)) {

            $this->newLine();
            $this->error(" Пропущенные файлы ");
            $this->newLine();

            $this->table(
                ['Count', 'File', 'Message'],
                $this->drop,
            );
        }

        $this->newLine();

        return 0;
    }

    /**
     * Метод проверки аудиофайла
     * 
     * @param array $data
     * @return null|string Текст ошибки проверки
     */
    public function createAndCheckAudio($data)
    {
        $file = CallDetailRecords::create($data);

        try {
            Incomings::updateDurationTime($file, $this->host);
        } catch (\FFMpeg\Exception\RuntimeException $e) {

            $error = $e->getMessage();

            $this->drop[] = [
                'count' => count($this->drop) + 1,
                'name' => $file,
                'message' => "<fg=red>{$error}</>",
            ];
        }
    }
}
