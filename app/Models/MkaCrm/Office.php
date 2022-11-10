<?php

namespace App\Models\MkaCrm;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Office extends Model
{
    use HasFactory;

    /**
     * Подключение по умолчанию
     * 
     * @var string
     */
    protected $connection = "mka_crm";

    /**
     * Наименование таблицы
     * 
     * @var string
     */
    protected $table = "_offices";

    /**
     * Типы данных
     * 
     * @var array
     */
    protected $casts = [
        'for_external_info' => "boolean",
    ];
}
