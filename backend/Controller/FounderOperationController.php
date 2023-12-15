<?php


namespace App\Http\Controllers\Api;


use App\Exceptions\ApiModelNotFoundException;
use App\Http\Controllers\ApiResponse\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Founder\FounderOperationRequest;
use App\Http\Resources\Relation\FounderOperationMaterialItem;
use App\Http\Resources\Relation\FounderOperationProductItem;
use App\Material;
use App\Models\Founder\Founder;
use App\Models\Founder\FounderOperation;
use App\Models\Founder\FounderOperationNomenclature;
use App\Product;
use App\Repositories\Api\Founder\Interfaces\FounderOperationRepositoryInterface;
use App\WarehouseMaterial;
use App\WarehouseProduct;
use Carbon\Carbon;
use EllipseSynergie\ApiResponse\Laravel\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FounderOperationController extends Controller
{
    protected $response;
    protected $founderOperation;
    private $founderOperationRepository;
    protected $per_page;
    protected $apiResponse;
    private $message_not_found;

    public function __construct(Response $response, ApiResponse $apiResponse, FounderOperation $founderOperation, FounderOperationRepositoryInterface $founderOperationRepository)
    {
        $this->middleware('auth:api');
        $this->middleware('permission:founderOperations.index')->only('index');
        $this->middleware('permission:founderOperations.create')->only('store');
        $this->middleware('permission:founderOperations.show')->only('show');
        $this->middleware('permission:founderOperations.update')->only('update');
        $this->middleware('permission:founderOperations.delete')->only(['destroy']);

        $this->response = $response;
        $this->apiResponse = $apiResponse;
        $this->founderOperation = $founderOperation;
        $this->founderOperationRepository = $founderOperationRepository;
        $this->per_page = request()->get( 'per_page',1000000);
        $this->message_not_found = trans('messages.not_found', ['name' => trans('messages.operation')]);
    }

    public function index()
    {
        $founderOperations = $this->founderOperation;

        if ($str = \request('search'))
        {
            $founderOperations = $founderOperations->search($str);
        }

        $founderOperations = $founderOperations->filter()->sort();

        $founderOperations = $founderOperations->select([
            'id', 'founder_id', 'start_date', 'operation_type_id', 'investment_type_id', 'output_type_id',
            'total_price', 'currency_id', 'rate',
            'created_at', 'updated_at',
        ]);

        $founderOperations = $founderOperations->with([
            'founder.user:id,name',
            'currency:id,name'
        ]);

        $founderOperations = $founderOperations->paginate($this->per_page);

        foreach ($founderOperations->items() as $item) {
            $item['operation_type'] = FounderOperation::getOperationTypeById($item->operation_type_id);
            $item['investment_type'] = FounderOperation::getInvestmentTypeById($item->investment_type_id);
            $item['output_type'] = FounderOperation::getOutputTypeById($item->output_type_id);

            // agar materialniy vlojeniya bolsa priyom protsentini hisoblash
            if ($item->operation_type_id == FounderOperation::OPERATION_TYPE_INVESTMENT_ID
                && $item->investment_type_id == FounderOperation::INVESTMENT_TYPE_MATERIAL_ID)
            {
                $total_quantity = floatval(FounderOperationNomenclature::where('founder_operation_id', $item->id)->sum('quantity'));

                $total_receive_products = floatval(WarehouseProduct::where('warehouse_productable_type', WarehouseProduct::WAREHOUSE_ABLE_TYPE_FOUNDER_OPERATION_NOMENCLATURE)
                    ->whereIn('warehouse_productable_id', $item->nomenclatures()->where('nomenclatureable_type', FounderOperationNomenclature::NOMENCLATUREABLE_TYPE_PRODUCT)->pluck('id')->toArray())
                    ->sum('receive'));

                $total_receive_materials = floatval(WarehouseMaterial::where('warehouse_materialable_type', WarehouseMaterial::ABLE_TYPE_FOUNDER_OPERATION_NOMENCLATURE)
                    ->whereIn('warehouse_materialable_id', $item->nomenclatures()->where('nomenclatureable_type', FounderOperationNomenclature::NOMENCLATUREABLE_TYPE_MATERIAL)->pluck('id')->toArray())
                    ->sum('total_coming'));

                $total_receive = $total_receive_materials + $total_receive_products;

                $performed = ($total_quantity > 0) ? round(100 * $total_receive / $total_quantity, 2) : 0;
            }
            // aks holda kassadan pul otgan protsentini hisoblash
            else {
                $real_amount = floatval($item->total_price * $item->rate);
                $total_paid = floatval(\App\Payment::where('paymentable_type', \App\Payment::ABLE_TYPE_FOUNDER_OPERATION)
                    ->where('paymentable_id', $item->id)
                    ->sum('amount'));

                $performed = ($real_amount > 0) ? round(100 * $total_paid / $real_amount, 2) : 0;
            }
            $item['performed'] = $performed;
        }

        return $this->response->withArray([
            'result' => [
                'success' => true,
                'data' => [
                    'founderOperations' => $founderOperations->items(),
                    'pagination' => [
                        'total' => $founderOperations->total(),
                        'current_page' => $founderOperations->currentPage(),
                    ],
                ],
            ]
        ]);
    }

    public function store(FounderOperationRequest $request)
    {
        $this->founderOperationRepository->store($request);

        return $this->response->withArray([
            'result' => [
                'success' => true,
                'message' => trans('messages.store_success',['name' => trans('messages.operation')]),
                'data'    => [],
            ]
        ]);
    }

    public function show(FounderOperation $founderOperation)
    {
        return $this->response->withArray([
            'result' => [
                'success' => true,
                'data'    => [
                    'founderOperation'  => new \App\Http\Resources\FounderOperation($founderOperation, true)
                ]
            ]
        ]);
    }

    public function update(FounderOperationRequest $request, FounderOperation $founderOperation)
    {
        DB::beginTransaction();
        try {
            $this->founderOperationRepository->update($request, $founderOperation);

            DB::commit();
        }
        catch (\Throwable $e) {
            DB::rollback();
            throw $e;
        }

        return $this->response->withArray([
            'result' => [
                'success' => true,
                'message' => trans('messages.update_success',['name' => trans('messages.operation')]),
                'data'    => [],
            ]
        ]);
    }

    public function destroy(FounderOperation $founderOperation)
    {
        DB::beginTransaction();
        try {
            // nomenklaturalar bolsa ularni oldin ochirish
            FounderOperationNomenclature::where('founder_operation_id', $founderOperation->id)->delete();

            $founderOperation->delete();

            DB::commit();
        }
        catch (\Throwable $e) {
            DB::rollback();
            throw $e;
        }

        return $this->response->withArray([
            'result' => [
                'success' => true,
                'message' => trans('messages.destroy_success',['name' => trans('messages.operation')]),
            ]
        ]);
    }

    public function getOperationTypes()
    {
        $operationTypes = FounderOperation::getOperationTypes();

        return $this->response->withArray([
            'result' => [
                'success' => true,
                'data' => [
                    'operationTypes' => $operationTypes,
                ],
            ],
        ]);
    }

    public function getInvestmentTypes()
    {
        $investmentTypes = FounderOperation::getInvestmentTypes();

        return $this->response->withArray([
            'result' => [
                'success' => true,
                'data' => [
                    'investmentTypes' => $investmentTypes,
                ],
            ],
        ]);
    }

    public function getOutputTypes()
    {
        $outputTypes = FounderOperation::getOutputTypes();

        return $this->response->withArray([
            'result' => [
                'success' => true,
                'data' => [
                    'outputTypes' => $outputTypes,
                ],
            ],
        ]);
    }

    public function getInvestmentHistoryOfFounder($founder_id)
    {
        if (!$founder = Founder::find($founder_id)) {
            throw new ApiModelNotFoundException($this->message_not_found);
        }

        $data = collect();

        $investments = $founder->operations()->with('currency:id,name', 'nomenclatures.currency:id,name,symbol,active')
            ->addSelect(['sum_investment_total_price' => DB::table('founder_operations AS fo_2')->select(DB::raw('COALESCE(SUM(fo_2.total_price * fo_2.rate), 0)'))
                ->where('operation_type_id', FounderOperation::OPERATION_TYPE_INVESTMENT_ID)
                ->whereColumn('fo_2.founder_id', 'founder_operations.founder_id')
                ->whereColumn('fo_2.start_date', '<=', 'founder_operations.start_date')
            ])
            ->addSelect(['sum_output_exit_total_price' => DB::table('founder_operations AS fo_3')->select(DB::raw('COALESCE(SUM(fo_3.total_price * fo_3.rate), 0)'))
                ->where('operation_type_id', FounderOperation::OPERATION_TYPE_OUTPUT_ID)
                ->where('output_type_id', FounderOperation::OUTPUT_TYPE_FOUNDER_EXIT_ID)
                ->whereColumn('fo_3.founder_id', 'founder_operations.founder_id')
                ->whereColumn('fo_3.start_date', '<=', 'founder_operations.start_date')
            ])
            ->where('operation_type_id', FounderOperation::OPERATION_TYPE_INVESTMENT_ID)
            ->orderBy('start_date', 'ASC')
            ->get();

        foreach ($investments as $investment) {
            // ushbu vlojeniyadan keyin yana vlojeniya bormi
//            $next_investment_model = FounderOperation::where('founder_id', $founder_id)
//                ->where('operation_type_id', FounderOperation::OPERATION_TYPE_INVESTMENT_ID)
//                ->where('start_date', '>', Carbon::parse($investment->start_date)->toDateTimeString())
//                ->orderBy('start_date', 'ASC')
//                ->first();

            /**
             * vlojeniya kiritganda uchreditelli dolyasi qancha bolganini hisoblash
             */
            $total_all_investment = floatval(FounderOperation::where('operation_type_id', FounderOperation::OPERATION_TYPE_INVESTMENT_ID)
                ->where('start_date', '<=', Carbon::parse($investment->start_date)->toDateTimeString())
                ->sum(DB::raw('total_price * rate')));

            $total_all_output_exit = floatval(FounderOperation::where('operation_type_id', FounderOperation::OPERATION_TYPE_OUTPUT_ID)
                ->where('output_type_id', FounderOperation::OUTPUT_TYPE_FOUNDER_EXIT_ID)
                ->where('start_date', '<=', Carbon::parse($investment->start_date)->toDateTimeString())
                ->sum(DB::raw('total_price * rate')));

            // shu vlojeniyagacha bolgan ustavnoy fond
            $charter_capital = $total_all_investment - $total_all_output_exit;

            $current_share_percentage = ($charter_capital > 0) ? round((100 * ($investment->sum_investment_total_price - $investment->sum_output_exit_total_price) / $charter_capital), 2) : 0;


            /**
             * Nomenklaturalarni ajratib olish
             */
            $materials_nomenclature = collect();
            $products_nomenclature = collect();
            if ($investment->investment_type_id == FounderOperation::INVESTMENT_TYPE_MATERIAL_ID) {
                foreach ($investment->nomenclatures as $nomenclature) {
                    if ($nomenclature->nomenclatureable_type == FounderOperationNomenclature::NOMENCLATUREABLE_TYPE_PRODUCT) {
                        $product_model = Product::select('id', 'name', 'measurement_id')->with('measurement:id,name')->find($nomenclature->nomenclatureable_id);
                        $products_nomenclature->push([
                            'product'   => $product_model,
                            'quantity'  => floatval($nomenclature->quantity),
                            'price'     => floatval($nomenclature->price),
                            'currency'  => $nomenclature->currency,
                            'rate'      => floatval($nomenclature->rate),
                        ]);
                    }
                    else if ($nomenclature->nomenclatureable_type == FounderOperationNomenclature::NOMENCLATUREABLE_TYPE_MATERIAL) {
                        $material_model = Material::select('id', 'name', 'measurement_id')->with('measurement:id,name')->find($nomenclature->nomenclatureable_id);
                        $materials_nomenclature->push([
                            'material'  => $material_model,
                            'quantity'  => floatval($nomenclature->quantity),
                            'price'     => floatval($nomenclature->price),
                            'currency'  => $nomenclature->currency,
                            'rate'      => floatval($nomenclature->rate),
                        ]);
                    }
                }
            }


            $data->push([
                'investment_type'           => FounderOperation::getInvestmentTypeById($investment->investment_type_id),
                'investment_type_id'        => $investment->investment_type_id,
                'start_date'                => date(Controller::ELEMENT_DATE_TIME_FORMAT, strtotime($investment->start_date)),
                //'end_date'                  => $next_investment_model ? (date(Controller::ELEMENT_DATE_TIME_FORMAT, strtotime($next_investment_model->start_date))) : '',
                'total_price'               => $investment->total_price,
                'currency'                  => $investment->currency,
                'rate'                      => $investment->rate,
                'current_share_percentage'  => $current_share_percentage,
                'products_nomenclature'     => $products_nomenclature,
                'materials_nomenclature'    => $materials_nomenclature,
            ]);
        }


        /**
         * Uchreditelni hozirgi (yani oxirgi) dolya protsentini hisoblash
         */
        $total_investment = floatval(FounderOperation::where('operation_type_id', FounderOperation::OPERATION_TYPE_INVESTMENT_ID)
            ->sum(DB::raw('total_price * rate')));

        $total_output_exit = floatval(FounderOperation::where('operation_type_id', FounderOperation::OPERATION_TYPE_OUTPUT_ID)
            ->where('output_type_id', FounderOperation::OUTPUT_TYPE_FOUNDER_EXIT_ID)
            ->sum(DB::raw('total_price * rate')));

        $charter_capital = $total_investment - $total_output_exit;

        $total_investment_of_founder = floatval(FounderOperation::where('operation_type_id', FounderOperation::OPERATION_TYPE_INVESTMENT_ID)
            ->where('founder_id', $founder_id)
            ->sum(DB::raw('total_price * rate')));

        $total_output_exit_of_founder = floatval(FounderOperation::where('operation_type_id', FounderOperation::OPERATION_TYPE_OUTPUT_ID)
            ->where('output_type_id', FounderOperation::OUTPUT_TYPE_FOUNDER_EXIT_ID)
            ->where('founder_id', $founder_id)
            ->sum(DB::raw('total_price * rate')));

        $share_percentage_present_time = ($charter_capital > 0) ? round((100 * ($total_investment_of_founder - $total_output_exit_of_founder) / $charter_capital), 2) : 0;


        return $this->response->withArray([
            'result' => [
                'success' => true,
                'data'    => [
                    'investments'  => $data,
                    'share_percentage_present_time' => $share_percentage_present_time
                ]
            ]
        ]);
    }

    public function deleteNomenclature(Request $request)
    {
        if( !$nomenclature = FounderOperationNomenclature::find($request['operation_nomenclature_id'])) {
            throw new ApiModelNotFoundException(trans('messages.not_found', ['name' => trans('messages.nomenclature')]));
        }

        DB::beginTransaction();
        try {
            // agar formadan bittalab nomeclatura ochirilsa operatsiyani summasiniyam ozgartirish. chunki soxranit qilmasdan chiqib ketishiyam mumkin
            if ($founder_operation = $nomenclature->founder_operation) {
                $nomenclature_total_price = floatval($nomenclature->quantity) * floatval($nomenclature->price) * floatval($nomenclature->rate);

                $founder_operation->total_price -= $nomenclature_total_price;

                $founder_operation->update();
            }

            $nomenclature->delete();

            DB::commit();
        }
        catch (\Throwable $e) {
            DB::rollback();
            throw $e;
        }

        return $this->response->withArray([
            'result' => [
                'success' => true,
                'message' => trans('messages.destroy_success', ['name' => trans('messages.nomenclature')]),
            ],
        ]);
    }

    public function getNomenclatures($operation_id)
    {
        if( !$founderOperation = FounderOperation::find($operation_id)) {
            throw new ApiModelNotFoundException($this->message_not_found);
        }

        $product_items = FounderOperationProductItem::collection($founderOperation->nomenclatures()->where('nomenclatureable_type', FounderOperationNomenclature::NOMENCLATUREABLE_TYPE_PRODUCT)->get());
        $material_items = FounderOperationMaterialItem::collection($founderOperation->nomenclatures()->where('nomenclatureable_type', FounderOperationNomenclature::NOMENCLATUREABLE_TYPE_MATERIAL)->get());

        return $this->response->withArray([
            'result' => [
                'success' => true,
                'product_items'     => $product_items,
                'material_items'    => $material_items,
            ],
        ]);
    }
}
