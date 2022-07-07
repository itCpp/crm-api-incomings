<?php

namespace App\Http\Controllers\Mikrotik;

use App\Models\MikrotikQueue;
use App\Models\MikrotikQueuesLimit;
use Illuminate\Http\Request;

class Cabinet extends Queues
{
    /**
     * Главная страница личного кабинета
     * 
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function main(Request $request)
    {
        $name = $request->session()->get('name');

        if (!$row = MikrotikQueuesLimit::whereName($name)->first())
            return abort(404);

        $month = now()->format("Y-m");
        $start = $row->start ?: $row->created_at->format("Y-m-d");
        $day = (int) now()->create($start)->format("j");

        $date = now()->create($month)->setDay($day);
        $start = $date->copy()->subMonth();
        $stop = $date->copy();

        $query = MikrotikQueue::where([
            ['name', $name],
            ['date', '>=', $start->format("Y-m-d")],
            ['date', '<', $stop->format("Y-m-d")]
        ]);

        $row->traffic = (int) $query->sum('downloads');
        $row->max = (int) $query->max('downloads');

        $query->get()->each(function ($day) use ($row, &$dates) {

            $day->traffic = $day->downloads;

            $day->percent = $row->max > 0 ? round($day->traffic * 100 / $row->max, 4) : 0;

            if ($day->percent > 0 and $day->percent < 1.5)
                $day->percent = 1.5;

            $dates[$day->date] = $day->toArray();
        });

        $day = $start->copy();

        for ($i = 0; $i < $start->diff($stop)->days; $i++) {

            $date = $day->format("Y-m-d");
            $traffic = $dates[$date]['traffic'] ?? 0;

            $days[] = [
                'date' => $date,
                'percent' => $dates[$date]['percent'] ?? 0,
                'traffic' => $traffic,
                'format' => $traffic > 0 ? $this->formatBytes($traffic) : null,
            ];

            $day->addDay();
        }

        $limit = $row->limit ?? self::LIMIT;
        $good = round($limit > 0 ? ($row->traffic * 100 / $limit) : 100, 2);

        if ($good > 100 and $limit > 0) {
            $overspending = 100 - round($limit * 100 / $row->traffic, 2);
            $bad = $overspending;
            $good = 100 - $bad;
        }

        $row->traffic_format = $this->formatBytes($row->traffic);
        $row->traffic_limit = $limit > 0 ? $this->formatBytes($limit) : null;
 
        return view('internet.cabinet', [
            'row' => $row,
            'days' => $days ?? [],
            'stop' => $stop->format("d.m.Y"),
            'progress' => [
                'good' => $good,
                'bad' => $bad ?? 0,
            ],
        ]);
    }
}
