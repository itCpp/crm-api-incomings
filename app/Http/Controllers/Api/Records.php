<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Middleware\Api\CheckInfoToken;
use App\Models\MkaCrm\CrmRequest;
use App\Models\MkaCrm\Office;
use Exception;
use Illuminate\Http\Request;

class Records extends Controller
{
    /**
     * Список офисов
     * 
     * @var
     */
    protected $offices;

    /**
     * Новый экземпляр контроллера.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(CheckInfoToken::class);

        $this->offices = Office::when((bool) request()->office, function ($query) {
            $query->where('id', request()->office);
        })->whereForExternalInfo(true)->get();
    }

    /**
     * Вывод записей
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illumiante\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $date = $this->isDate($request->date) ? $request->date : now()->format("Y-m-d");
        $date = now()->create($date);

        if ((int) $date->copy()->format("j") >= 16) {
            $start = $date->copy()->setDay(16)->format("Y-m-d");
            $stop = $date->copy()->endOfMonth()->format("Y-m-d");
        } else {
            $start = $date->copy()->startOfMonth()->format("Y-m-d");
            $stop = $date->copy()->setDay(15)->format("Y-m-d");
        }

        $this->offices->each(function ($row) use (&$addresses) {
            $addresses[] = $row->id;
        });

        $data = CrmRequest::select()
            ->where([
                ['noView', 0],
                ['del', '=', ""],
            ])
            ->whereIn('state', ['zapis', 'podtverjden'])
            ->where('rdate', $date->copy()->format("Y-m-d"))
            // ->whereBetween('rdate', [$start, $stop])
            ->whereIn('address', $addresses ?? [])
            ->orderBy('rdate')
            ->orderBy('time')
            ->lazy()
            ->map(function ($row) {

                $response = [
                    'fio' => $row->name,
                    'theme' => $row->theme,
                    'date' => $row->rdate . " " . $row->time,
                    'confirmed' => $row->state == "podtverjden",
                    'office_id' => $row->address,
                ];

                if (request()->get('getOfficeIcon'))
                    $response['office_icon'] = $this->getOfficeIcon($row->address);

                return $response;
            })
            ->toArray();

        return response()->json($data);
    }

    /**
     * Получает икноку офиса
     * 
     * @param  int  $office_id
     * @return null|string
     */
    public function getOfficeIcon($office_id)
    {
        if (!empty($this->get_office_icon[$office_id]))
            return $this->get_office_icon[$office_id];

        $fund = $this->offices->search(function ($row, $key) use ($office_id) {
            return $row->id == $office_id;
        });

        $office = $this->offices[$fund] ?? null;

        if ($office === false or !(bool) ($office->icon ?? null))
            return $this->get_office_icon[$office_id] = null;

        $icon = null;

        try {
            $imageSize = getimagesize($office->icon);
            $imageData = base64_encode(file_get_contents($office->icon));
            $icon = "data:{$imageSize['mime']};base64,{$imageData}";
        } catch (Exception $e) {
            $icon = $e->getMessage();
        }

        return $this->get_office_icon[$office_id] = $icon;
    }
}
