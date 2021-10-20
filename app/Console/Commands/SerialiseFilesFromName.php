<?php

namespace App\Console\Commands;

use App\Http\Controllers\Controller;
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

        $added = [];
        $drop = [];

        foreach ($files as $file) {

            $bar->setMessage($file);

            $parce = explode("-", $file);

            if (count($parce) >= 4) {

                $phone = null;
                $type = null;

                if ($check = Controller::checkPhone($parce[0], 3)) {
                    $phone = $check;
                    $type = "out";
                } elseif ($check = Controller::checkPhone($parce[1], 3)) {
                    $phone = $check;
                    $type = "in";
                }

                $date = null;

                


                if (!$phone) {
                    $drop[] = [
                        'name' => $file,
                        'phone' => true,
                        'data' => $parce,
                    ];
                }
            } else {
                $drop[] = [
                    'name' => $file,
                    'parce' => true,
                    'data' => $parce,
                ];
            }

            $bar->advance();

            // usleep(300);

        }

        $bar->setMessage("");
        $bar->finish();

        $this->newLine();

        if (count($drop)) {

            $this->newLine();
            $this->error(" Пропущенные файлы ");
            $this->newLine();

            foreach ($drop as $file) {

                $message = "";

                if (isset($file['phone']))
                    $message = " <fg=red>Номер не определен</>";

                if (isset($file['parce']))
                    $message = " <fg=red>Ошибка наименования</>";

                $this->line("{$file['name']}{$message}");

            }
        }

        $this->newLine();

        return 0;
    }
}
