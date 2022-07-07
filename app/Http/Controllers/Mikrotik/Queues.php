<?php

namespace App\Http\Controllers\Mikrotik;

use App\Http\Controllers\Controller;
use App\Models\MikrotikAdditionalTraffic;
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
    const LIMIT_DOWN = "16";

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
            $setting = MikrotikQueuesLimit::firstOrCreate(['name' => $name], [
                'limit_up' => self::LIMIT_UP,
                'limit_down' => self::LIMIT_UP,
                'limit' => 0,
                'start' => now()->format("Y-m-d"),
            ]);
        }

        /** Выводит верхний предел скорости при неограниченном трафике */
        if ($setting) {
            if ($setting->limit === 0)
                return $setting->limit_up ?? self::LIMIT_UP;
        }

        $limit = ($setting->limit ?? self::LIMIT) + $this->getAddedLimit($setting);
        $total = $this->getTotalTraff($setting);

        return ($total > $limit)
            ? ($setting->limit_down ?? self::LIMIT_DOWN)
            : ($setting->limit_up ?? self::LIMIT_UP);
    }

    /**
     * Подсчет дополнительного выделенного трафика
     * 
     * @param  \App\Models\MikrotikQueuesLimit $row
     * @return int
     */
    public function getAddedLimit(MikrotikQueuesLimit $row)
    {
        $date = $this->getDatesPeriod($row);

        return $this->getAdditional($row->id, $date['start'], $date['stop']);
    }

    /**
     * Подсчитывает использованный трафик за период
     * 
     * @param  \App\Models\MikrotikQueuesLimit $row
     * @return int
     */
    public function getTotalTraff(MikrotikQueuesLimit $row)
    {
        $date = $this->getDatesPeriod($row);

        return (int) MikrotikQueue::where([
            ['name', $row->name],
            ['date', '>=', $date['start']],
            ['date', '<', $date['stop']],
        ])->sum('downloads');
    }

    /**
     * Формирует даты отчетного периода для потребителя
     * 
     * @param  \App\Models\MikrotikQueuesLimit $row
     * @return array
     */
    public function getDatesPeriod(MikrotikQueuesLimit $row)
    {
        if (!empty($this->dates_period))
            return $this->dates_period;

        $start = now()->create($row->start ?: $row->created_at->format("Y-m-d"));

        $day_start = (int) $start->format("j");
        $day_now = (int) now()->format("j");

        if ($day_start > $day_now) {
            $date_start = now()->setDay($day_start)->subMonth()->format("Y-m-d");
            $date_stop = now()->setDay($day_start)->format("Y-m-d");
        } else {
            $date_start = now()->setDay($day_start)->format("Y-m-d");
            $date_stop = now()->setDay($day_start)->addMonth()->format("Y-m-d");
        }

        return $this->dates_period = [
            'start' => $date_start,
            'stop' => $date_stop,
        ];
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

    /**
     * Выводит объём дополнительного трафика
     * 
     * @param  int $id
     * @param  string $start
     * @param  string $stop
     * @return int
     */
    public function getAdditional($id, $start, $stop)
    {
        return (int) MikrotikAdditionalTraffic::where([
            ['queue_id', $id],
            ['date', '>=', $start],
            ['date', '<', $stop]
        ])->sum('traffic');
    }
}
