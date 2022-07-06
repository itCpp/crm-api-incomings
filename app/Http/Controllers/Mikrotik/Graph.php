<?php

namespace App\Http\Controllers\Mikrotik;

use App\Models\MikrotikQueue;
use App\Models\MikrotikQueuesLimit;
use Illuminate\Http\Request;

class Graph extends Queues
{
    /**
     * Вывод данных для грфика расхода траффика
     * 
     * @param  \Illumiante\Http\Request $request
     * @param  string $name
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request, $name)
    {
        $max = 0;
        $sum = 0;
        $month = ($request->month ? now()->create($request->month) : now())->format("Y-m");

        $data = MikrotikQueue::selectRaw("SUM(downloads) as traf, date")
            ->where([
                ['name', $name],
                ['month', $month],
            ])
            ->groupBy('date')
            ->get()
            ->each(function ($row) use (&$max, &$sum) {

                $sum += $row->traf;

                if ($max < $row->traf)
                    $max = $row->traf;
            });

        $traf = [];

        $data->map(function ($row) use ($max, &$traf) {

            $row->percent = $max > 0 ? round($row->traf * 100 / $max, 4) : 0;

            if ($row->percent > 0 and $row->percent < 1.5)
                $row->percent = 1.5;

            $traf[$row->date] = $row;

            return $row;
        });

        for ($i = 1; $i <= (int) now()->create($month)->format("t"); $i++) {

            $date = now()->create($month)->setDay($i)->format("Y-m-d");

            $rows[] = [
                'traf' => $traf[$date]->traf ?? 0,
                'traffic' => $this->formatBytes($traf[$date]->traf ?? 0, 1),
                'percent' => $traf[$date]->percent ?? 0,
                'date' => $date,
            ];
        }

        $queue = MikrotikQueuesLimit::whereName($name)->first();
        $limit = $queue->limit ?? self::LIMIT;

        $good = round($limit > 0 ? ($sum * 100 / $limit) : 100, 2);

        if ($good > 100 and $limit > 0) {
            $overspending = 100 - round($limit * 100 / $sum, 2);
            $bad = $overspending;
            $good = 100 - $bad;
        }

        return view('traf', [
            'queue' => $queue,
            'rows' => $rows ?? [],
            'name' => $queue->title ?? $name,
            'limit' => $this->getLimit($queue, $month),
            'month' => $this->getRusMonth((int) now()->create($month)->format("n")),
            'traffic' => $limit,
            'sum' => $sum,
            'good' => $good,
            'bad' => $bad ?? 0,
            'prev' => $this->getPrevLink($month),
            'next' => $this->getNextLink($month),
        ]);
    }

    /**
     * Выводит ссылку на предыдущий месяц
     * 
     * @param  string $month
     * @return null|string
     */
    public function getPrevLink($month)
    {
        $month = now()->create($month)->subMonth()->format("Y-m");

        return $this->getLink(MikrotikQueue::where('month', $month)->first()->month ?? null);
    }

    /**
     * Выводит ссылку на следующий месяц
     * 
     * @param  string $month
     * @return null|string
     */
    public function getNextLink($month)
    {
        $month = now()->create($month)->addMonth()->format("Y-m");

        return $this->getLink(MikrotikQueue::where('month', $month)->first()->month ?? null);
    }

    /**
     * Формирует ссылку для просмотра архива траффика
     * 
     * @param  null|string $month
     * @return null|string
     */
    public function getLink($month)
    {
        if (!$month)
            return null;

        $date = now()->create($month);

        $name = mb_convert_case(
            $this->getRusMonth((int) $date->copy()->format("n")),
            MB_CASE_TITLE,
            'UTF-8'
        );

        $name .= " " . $date->copy()->format("Y");

        return "<a class=\"btn btn-primary btn-sm mx-2\" href=\"?month={$month}\" role=\"button\">{$name}</a>";
    }
}
