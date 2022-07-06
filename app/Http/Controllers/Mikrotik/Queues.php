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

    /** Нижний предел ограничения скорости */
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

        return response($this->getLimit($row->name))
            ->header('Content-Type', 'text/plain');
    }

    /**
     * Получить текущий лимит по имени
     * 
     * @param  string $name
     * @param  null|string $month
     * @return string
     */
    public function getLimit($name, $month = null)
    {
        if ($name instanceof MikrotikQueuesLimit) {
            $setting = $name;
        } else {
            $setting = MikrotikQueuesLimit::where('name', $name)->first();
        }

        if ($setting) {
            if ($setting->limit === 0)
                return $setting->limit_up ?? self::LIMIT_UP;
        }

        $limit = ($this->getTotal($setting->name, $month) > ($setting->limit ?? self::LIMIT))
            ? ($setting->limit_down ?? self::LIMIT_DOWN)
            : ($setting->limit_up ?? self::LIMIT_UP);

        return $limit;
    }

    /**
     * Получает общий объём траффика
     * 
     * @param  string $name
     * @param  null|string $month
     * @return int
     */
    public function getTotal($name, $month = null)
    {
        return MikrotikQueue::where([
            ['name', $name],
            ['month', $month ?: now()->format("Y-m")],
        ])->sum('downloads');
    }
}
