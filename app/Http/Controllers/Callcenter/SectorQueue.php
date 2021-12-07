<?php

namespace App\Http\Controllers\Callcenter;

use App\Http\Controllers\Controller;
use App\Models\CallsSectorQueue;
use App\Models\CallsSectorSettings;
use Illuminate\Http\Request;

class SectorQueue extends Controller
{
    /**
     * Настройки секторов
     * 
     * @var array
     */
    protected $sectors;

    /**
     * Создание экземпляра объекта
     * 
     * @return void
     */
    public function __construct()
    {
        $only = CallsSectorSettings::whereOnlyQueue(1)->whereActive(1)->first();
        $sectors = [];

        if ($only) {
            $sectors = [$only->toArray()];
        } else {
            $sectors = CallsSectorSettings::whereActive(1)->get()->map(function ($row) {
                return $row->toArray();
            })->toArray();
        }

        $this->sectors = count($sectors) ? $sectors : $this->getDefaultSectorsArray();
    }

    /**
     * Стандартные значения секторов
     * 
     * @return array
     */
    public function getDefaultSectorsArray()
    {
        return [
            [
                'id' => 2,
                'name' => "А",
                'count_change_queue' => 2, // Количество звонков до смены сектора 
            ],
            [
                'id' => 3,
                'name' => "CH1",
                'count_change_queue' => 1, // Количество звонков до смены сектора 
            ],
        ];
    }

    /**
     * Определение сектора для переадресации звонка
     * 
     * @param Request $request
     * @return int
     */
    public function getSector(Request $request)
    {
        $data = $this->getQueueData($request);

        $data->counter++;
        $data = $this->getNextSectorId($data);

        $data->save();

        return (int) $data->last_sector_id;
    }

    /**
     * Вывод статистических данных для сектора
     * 
     * @param Request $request
     * @return CallsSectorQueue
     */
    public function getQueueData(Request $request)
    {
        return CallsSectorQueue::firstOrCreate([
            'sip_server' => $request->ip(),
            'date' => date("Y-m-d"),
        ]);
    }

    /**
     * Определение сектора для звонка
     * 
     * @param CallsSectorQueue $data
     * @return CallsSectorQueue
     */
    public function getNextSectorId($data)
    {
        if (!$data->last_sector_id)
            return $this->getFirstCallQueue($data);

        return $this->getNextSector($data);
    }

    /**
     * Формирование данных для первого звонка
     * 
     * @param CallsSectorQueue $data
     * @return CallsSectorQueue
     */
    public function getFirstCallQueue($data)
    {
        $sector = $this->getSectorData();

        $data->last_sector_id = $sector['id'] ?? null;
        $data->next_to_count = 1;
        $data->data_counters = $this->setStatCounters($data->data_counters, $sector['id']);

        return $data;
    }

    /**
     * Определение следующего сектора по очереди
     * 
     * @param CallsSectorQueue $data
     * @return CallsSectorQueue
     */
    public function getNextSector($data)
    {
        $last_sector = $this->getSectorData($data->last_sector_id, false);

        $data->next_to_count++;

        if ($data->next_to_count > $last_sector['count_change_queue'])
            $data = $this->changeNextSector($data);

        $data->data_counters = $this->setStatCounters($data->data_counters, $data->last_sector_id);

        return $data;
    }

    /**
     * Смена сектора
     * 
     * @param CallsSectorQueue $data
     * @return CallsSectorQueue
     */
    public function changeNextSector($data)
    {
        $sector = $this->getSectorData($data->last_sector_id);

        $data->last_sector_id = $sector['id'] ?? null;
        $data->next_to_count = 1;

        return $data;
    }

    /**
     * Поиск сектора
     * 
     * @param null|int $id
     * @param bool $next Флаг поиска следующего сектора
     * @return array
     */
    public function getSectorData($id = null, $next = true)
    {
        $return_next = false;
        $data = null;

        foreach ($this->sectors as $sector) {

            if ($id === null or $return_next or ($sector['id'] == $id and !$next))
                return $sector;

            if ($sector['id'] == $id and $next)
                $return_next = true;
        }

        return $data ?? $this->getSectorData();
    }

    /**
     * Прибавка счетчика по секторам
     * 
     * @param array $data
     * @param int $id
     * @return array
     */
    public function setStatCounters($data, $id)
    {
        if (empty($data[$id]))
            $data[$id] = 0;

        $data[$id]++;

        return $data;
    }
}
