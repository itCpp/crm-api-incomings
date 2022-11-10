<?php

namespace App\Models\MkaCrm;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CrmRequest extends Model
{
    use HasFactory;

    /**
     * Подключение по умолчанию
     * 
     * @var string
     */
    protected $connection = "mka_crm";
}
