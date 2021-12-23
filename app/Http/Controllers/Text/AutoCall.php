<?php

namespace App\Http\Controllers\Text;

use Exception;
use App\Http\Controllers\Controller;
use App\Models\IncomingTextRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AutoCall extends Controller
{
    /**
     * Экземпляр модели текстового события
     * 
     * @var \App\Models\IncomingTextRequest
     */
    protected $row;

    /**
     * Настройки подключения к АМИ Asterisk
     * 
     * @var array
     */
    protected $asterisk;

    /**
     * Объявление экземпляра объекта
     * 
     * @param \App\Models\IncomingTextRequest $row
     * @return void
     */
    public function __construct(IncomingTextRequest $row)
    {
        $this->row = $row;

        $this->asterisk = config('asterisk.' . config('asterisk.default'));
    }

    /**
     * Отправка события звонка
     * 
     * @param string $phone
     * @return $this
     */
    public function send($phone)
    {
        $host = $this->asterisk['host'] ?? null;
        $port = $this->asterisk['port'] ?? null;

        $errno = 0;
        $errstr = "Неизвестная ошибка";

        Log::channel('autocall')->info("Start autocall $phone");

        $conn = fsockopen($host, $port, $errno, $errstr, 10)
            or throw new Exception("Connection to [$host]:[$port] failed");

        if (!$conn)
            throw new Exception("Error fsockopen() {$errstr} ({$errno})");

        $username = $this->asterisk['username'] ?? null;
        $secret = $this->asterisk['secret'] ?? null;

        $cid = env("ASTERISK_AMI_CID", "incomingtextadmin");
        $num = $this->checkPhone($phone, 3) ?? null;

        if (!$num) {
            fclose($conn);
            throw new Exception("Неправильный номер телефона");
        }

        fputs($conn, "Action: login\r\n");
        fputs($conn, "Username: $username\r\n");
        fputs($conn, "Secret: $secret\r\n");
        fputs($conn, "Events: off\r\n\r\n");

        usleep(500);

        fputs($conn, "Action: Originate\r\n");
        fputs($conn, "Channel: SIP/$cid\r\n");
        fputs($conn, "Callerid: $cid\r\n");
        fputs($conn, "Timeout: 15000\r\n");
        fputs($conn, "Context: fondsp\r\n");
        fputs($conn, "Exten: $num\r\n");
        fputs($conn, "Priority: 1\r\n\r\n");
        fputs($conn, "Async: yes\r\n\r\n");
        fputs($conn, "Action: Logoff\r\n\r\n");

        usleep(500);

        fclose($conn);

        Log::channel('autocall')->info("Sent commands autocall $num to $cid");
    }
}
