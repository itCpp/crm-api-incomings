<?php

namespace App\Http\Controllers\Mikrotik;

use App\Http\Controllers\Controller;
use App\Models\MikrotikQueue;
use App\Models\MikrotikQueuesLimit;
use Illuminate\Http\Request;

class Queues extends Controller
{
    /** Значение лимита по умолчанию  @var int */
    const LIMIT = 21474836480;

    /** Верхний предел ограничения скорости */
    const LIMIT_UP = "100M";

    /** Верхний предел ограничения скорости */
    const LIMIT_DOWN = "128K";

    /**
     * Прибавляет счетчик
     * 
     * @param  \Illuminate\Http\Request $request
     * @param  string $upload
     * @param  string $download
     * @param  string $name
     * @return \Illuminate\Http\Response
     */
    public function set(Request $request, $upload, $download, $name)
    {
        $date = now()->format("Y-m-d");
        $month = now()->format("Y-m");

        $row = MikrotikQueue::firstOrNew(
            ['name' => $name, 'date' => $date],
            ['month' => $month]
        );

        $row->uploads += $upload;
        $row->downloads += $download;

        $row->save();

        $bytes = $this->getTotal($row->name);

        $setting = MikrotikQueuesLimit::where('name', $row->name)->first();

        $limit = ($bytes > ($setting->limit ?? self::LIMIT))
            ? ($setting->limit_down ?? self::LIMIT_DOWN)
            : ($setting->limit_up ?? self::LIMIT_UP);

        return response($limit)->header('Content-Type', 'text/plain');
    }

    /**
     * Получает общий объём траффика
     * 
     * @param  string $name
     */
    public function getTotal($name)
    {
        return MikrotikQueue::where([
            ['name', $name],
            ['month', now()->format("Y-m")],
        ])->sum('downloads');
    }
}
