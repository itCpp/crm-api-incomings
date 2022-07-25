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
        $name = $request->name;
        // $request->session()->get('name');

        if (!$row = MikrotikQueuesLimit::whereName($name)->first())
            return abort(404);

        $request->name = $name;
        $request->row = $row;

        $start = $row->start ?: $row->created_at->format("Y-m-d");
        $day = (int) now()->create($start)->format("j");

        if ($request->month)
            $month = $request->month;
        else if ($day < (int) now()->format("j"))
            $month = now()->addMonth()->format("Y-m");
        else
            $month = now()->format("Y-m");

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

        $row->limit_add = $this->getAdditional($row->id, $start->format("Y-m-d"), $stop->format("Y-m-d"));

        $limit = $row->limit ?? self::LIMIT;
        $good = round($limit > 0 ? ($row->traffic * 100 / $limit) : 100, 2);

        $limit += $row->limit_add;

        if ($good > 100 and $row->limit_add > 0) {

            $added = round($row->limit_add * 100 / $limit, 2);
            $add = ($good - 100);

            if ($added < $add) {
                $good -= $added;
                $add = $added;
            } else {
                $good = 100 - $add;
            }
        }

        if ($good > 100 and $limit > 0) {
            $bad = 100 - round($limit * 100 / $row->traffic, 2);
            $good = 100 - $bad;
        }

        $row->traffic_format = $this->formatBytes($row->traffic);
        $row->traffic_limit = $limit > 0 ? $this->formatBytes($limit) : null;

        setlocale(LC_ALL, 'ru_RU', 'ru_RU.UTF-8', 'ru', 'russian');  

        return view('internet.cabinet', [
            'row' => $row,
            'days' => $days ?? [],
            'stop' => $stop->format("d.m.Y"),
            'progress' => [
                'good' => $good,
                'add' => $add ?? 0,
                'bad' => $bad ?? 0,
            ],
            'links' => $this->getLinks($request),
            'month' => $month,
            'period' => "Расход с " . $start->format("d.m.Y") . " по " . $stop->subDay()->format("d.m.Y")
        ]);
    }

    /**
     * Выводит ссылки для просмотра архивных данных
     * 
     * @param  \Illuminate\Http\Request $request
     * @return string|null
     */
    public function getLinks(Request $request)
    {
        if (!($request->row instanceof  MikrotikQueuesLimit))
            $request->row = MikrotikQueuesLimit::whereName($request->name)->first();

        if (!$request->row)
            return null;

        $start = now()->create($request->row->start ?: $request->row->created_at->format("Y-m-d"));
        $month = $request->month ?: now()->format("Y-m");

        if ((int) now()->format("j") >= (int) $start->format("j")) {
            $now_month = now()->create($month)->format("Y-m");
        } else {
            $now_month = now()->create($month)->subMonth()->format("Y-m");
        }

        $prev = $next = $now = "";

        MikrotikQueue::select('month')
            ->where([
                ['name', $request->row->name],
                ['month', '<=', $now_month],
            ])
            ->distinct()
            ->limit(3)
            ->orderBy('month')
            ->get()
            ->each(function ($row) use (&$prev) {
                $prev .= $this->getHtmlLink($row->month);
            });

        MikrotikQueue::select('month')
            ->where([
                ['name', $request->row->name],
                ['month', '>', $now_month],
                ['month', '!=', $month]
            ])
            ->distinct()
            ->limit(2)
            ->get()
            ->each(function ($row) use (&$next) {
                $next .= $this->getHtmlLink($row->month);
            });

        if (((bool) $prev or (bool) $next) and $request->month) {
            $now = "<a class=\"btn btn-warning btn-sm ms-3 me-1\" href=\"?month\">Текущий</a>";
        }

        $links = $prev . $next . $now;

        return (bool) $links ? $links : null;
    }

    /**
     * Возвращает html разметку ссылки смены месяца
     * 
     * @param  string $month
     * @return string
     */
    public function getHtmlLink($month)
    {
        $title = strftime("%b %Y", now()->create($month)->timestamp); 

        return "<a class=\"btn btn-success btn-sm mx-1\" href=\"?month={$month}\">{$title}</a>";
    }


}
