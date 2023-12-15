<?php

namespace App\Models\Founder;

use App\Currency;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\FunctionTrait;
use App\Http\Controllers\Traits\ScopeTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FounderOperationNomenclature extends Model
{
    use ScopeTrait, FunctionTrait;

    CONST NOMENCLATUREABLE_TYPE_MATERIAL = 'materials';
    CONST NOMENCLATUREABLE_TYPE_PRODUCT = 'products';

    protected $casts = [
        'quantity'      => 'float',
        'price'         => 'float',
        'rate'          => 'float',
        'start_date'    => 'datetime:' . Controller::ELEMENT_DATE_TIME_FORMAT,
        'created_at'    => 'datetime:' . Controller::ELEMENT_DATE_TIME_FORMAT,
        'updated_at'    => 'datetime:' . Controller::ELEMENT_DATE_TIME_FORMAT,
        'receive'       => 'float',
        'not_receive'   => 'float',
        'remainder'     => 'float',
    ];

    protected $fillable = [
        'founder_operation_id', 'nomenclatureable_type', 'nomenclatureable_id',
        'quantity', 'price', 'currency_id', 'rate'
    ];


    /**
     * Relations
     */
    public function founder_operation() {
        return $this->belongsTo(FounderOperation::class);
    }

    public function nomenclatureable() {
        return $this->morphTo();
    }

    public function currency() {
        return $this->belongsTo(Currency::class);
    }
}
