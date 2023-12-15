<?php

namespace App\Models\Founder;

use App\Currency;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\FunctionTrait;
use App\Http\Controllers\Traits\ScopeTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class FounderOperation extends Model
{
    use ScopeTrait, FunctionTrait;

    const TABLE_NAME = 'founder_operations';

    protected $casts = [
        'total_price'   => 'float',
        'rate'          => 'float',
        'start_date'    => 'datetime:' . Controller::ELEMENT_DATE_TIME_FORMAT,
        'created_at'    => 'datetime:' . Controller::ELEMENT_DATE_TIME_FORMAT,
        'updated_at'    => 'datetime:' . Controller::ELEMENT_DATE_TIME_FORMAT,
    ];

    protected $fillable = [
        'founder_id', 'start_date', 'operation_type_id', 'investment_type_id', 'output_type_id',
        'total_price', 'currency_id', 'rate'
    ];


    /**
     * Operation type IDs
     */
    const OPERATION_TYPE_INVESTMENT_ID = 1;
    const OPERATION_TYPE_OUTPUT_ID = 2;

    /**
     * Investment type IDs
     */
    const INVESTMENT_TYPE_FINANCIAL_ID = 1;
    const INVESTMENT_TYPE_MATERIAL_ID = 2;

    /**
     * Output type IDs
     */
    const OUTPUT_TYPE_PAYMENT_OF_DIVIDENDS_ID = 1;
    const OUTPUT_TYPE_FOUNDER_EXIT_ID = 2;


    /**
     * Relations
     */
    public function founder() {
        return $this->belongsTo(Founder::class);
    }

    public function currency() {
        return $this->belongsTo(Currency::class);
    }

    public function nomenclatures() {
        return $this->hasMany(FounderOperationNomenclature::class, 'founder_operation_id');
    }


    public function scopeFilter($query)
    {
        if ($filter = request('id')) {
            $query = $query->where('id', (int)$filter);
        }
        if ($filter = request('founder_id')) {
            $query = $query->where('founder_id', $filter);
        }
        if ($filter = request('start_date')){
            $query = $query->whereDate('start_date','=',Carbon::parse($filter)->toDateString());
        }
        if ($filter = request('operation_type_id')) {
            $query = $query->where('operation_type_id', $filter);
        }
        if ($filter = request('investment_type_id')) {
            $query = $query->where('investment_type_id', $filter);
        }
        if ($filter = request('output_type_id')) {
            $query = $query->where('output_type_id', $filter);
        }
        if ($filter = request('total_price')) {
            $query = $query->where('total_price', 'like', '%' . $filter . '%');
        }
        if ($filter = request('currency_id')) {
            $query = $query->where('currency_id', $filter);
        }
        if ($filter = request('rate')) {
            $query = $query->where('rate', 'like', '%' . $filter . '%');
        }
        if ($filter = request('created_at')){
            $query = $query->whereDate('created_at','=',Carbon::parse($filter)->toDateString());
        }
        if ($filter = request('updated_at')){
            $query = $query->whereDate('updated_at','=',Carbon::parse($filter)->toDateString());
        }

        return $query;
    }


    /**
     * Custom functions
     */
    public static function getOperationTypes()
    {
        return [
            ['id'=>self::OPERATION_TYPE_INVESTMENT_ID, 'name' => __('messages.attachment')],
            ['id'=>self::OPERATION_TYPE_OUTPUT_ID, 'name' => __('messages.output')],
        ];
    }
    public static function getOperationTypeById($operation_type_id = null){
        $operationTypes = self::getOperationTypes();
        foreach ($operationTypes as $operationType) {
            if ($operation_type_id == $operationType['id']) {
                return $operationType;
            }
        }
        return null;
    }

    public static function getInvestmentTypes()
    {
        return [
            ['id'=>self::INVESTMENT_TYPE_FINANCIAL_ID, 'name' => __('messages.financial')],
            ['id'=>self::INVESTMENT_TYPE_MATERIAL_ID, 'name' => __('messages.investment_type_material')],
        ];
    }
    public static function getInvestmentTypeById($investment_type_id = null){
        $investmentTypes = self::getInvestmentTypes();
        foreach ($investmentTypes as $investmentType) {
            if ($investment_type_id == $investmentType['id']) {
                return $investmentType;
            }
        }
        return null;
    }

    public static function getOutputTypes()
    {
        return [
            ['id'=>self::OUTPUT_TYPE_PAYMENT_OF_DIVIDENDS_ID, 'name' => __('messages.Payment of dividends')],
            ['id'=>self::OUTPUT_TYPE_FOUNDER_EXIT_ID, 'name' => __('messages.founder exit')],
        ];
    }
    public static function getOutputTypeById($output_type_id = null){
        $outputTypes = self::getOutputTypes();
        foreach ($outputTypes as $outputType) {
            if ($output_type_id == $outputType['id']) {
                return $outputType;
            }
        }
        return null;
    }
}
