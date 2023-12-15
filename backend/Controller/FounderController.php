<?php


namespace App\Http\Controllers\Api;


use App\Exceptions\ApiModelNotFoundException;
use App\Http\Controllers\ApiResponse\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Founder\FounderRequest;
use App\Models\Founder\Founder;
use App\Models\Founder\FounderOperation;
use Carbon\Carbon;
use EllipseSynergie\ApiResponse\Laravel\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FounderController extends Controller
{
    protected $response;
    protected $founder;
    protected $per_page;
    protected $apiResponse;
    private $message_not_found;

    public function __construct(Response $response, ApiResponse $apiResponse, Founder $founder)
    {
        $this->middleware('auth:api');
        $this->middleware('permission:founders.index')->only('index');
        $this->middleware('permission:founders.create')->only('store');
        $this->middleware('permission:founders.show')->only('show');
        $this->middleware('permission:founders.update')->only('update');
        $this->middleware('permission:founders.delete')->only(['destroy']);

        $this->response = $response;
        $this->apiResponse = $apiResponse;
        $this->founder = $founder;
        $this->per_page = request()->get( 'per_page',1000000);
        $this->message_not_found = trans('messages.not_found', ['name' => trans('messages.founder')]);
    }

    public function index()
    {
        $total_investment = floatval(FounderOperation::where('operation_type_id', FounderOperation::OPERATION_TYPE_INVESTMENT_ID)
            ->sum(DB::raw('total_price * rate')));

        $total_output_exit = floatval(FounderOperation::where('operation_type_id', FounderOperation::OPERATION_TYPE_OUTPUT_ID)
            ->where('output_type_id', FounderOperation::OUTPUT_TYPE_FOUNDER_EXIT_ID)
            ->sum(DB::raw('total_price * rate')));

        $charter_capital = $total_investment - $total_output_exit;

        $founders = $this->founder;

        if ($str = \request('search'))
        {
            $founders = $founders->search($str);
        }

        $founders = $founders->filter()->sort();

        $investments_join = DB::table('founder_operations')
            ->select('founder_id', DB::raw('SUM(total_price * rate) as total_price'))
            ->where('operation_type_id', FounderOperation::OPERATION_TYPE_INVESTMENT_ID)
            ->groupBy('founder_id');

        $output_exit_join = DB::table('founder_operations')
            ->select('founder_id', DB::raw('SUM(total_price * rate) as total_price'))
            ->where('operation_type_id', FounderOperation::OPERATION_TYPE_OUTPUT_ID)
            ->where('output_type_id', FounderOperation::OUTPUT_TYPE_FOUNDER_EXIT_ID)
            ->groupBy('founder_id');

        $founders = $founders->leftJoinSub($investments_join, 'investments', function ($join) {
                $join->on('founders.id', 'investments.founder_id');
            })
            ->leftJoinSub($output_exit_join, 'output_exits', function ($join) {
                $join->on('founders.id', 'output_exits.founder_id');
            });

        $founders = $founders->select([
            'founders.id AS id', 'user_id', 'is_active', 'comment',
            'founders.created_at AS created_at', 'founders.updated_at AS updated_at',
            DB::raw('(COALESCE(investments.total_price, 0) - COALESCE(output_exits.total_price, 0)) AS total_price'),
            DB::raw('CASE WHEN ' . $charter_capital . ' > 0 THEN round(CAST(float8 (100 * (COALESCE(investments.total_price, 0) - COALESCE(output_exits.total_price, 0)) / ' . $charter_capital . ') AS numeric), 2) ELSE 0 END AS share_percentage')
        ]);

        $founders = $founders->with([
            'user:id,name,phone,email',
        ]);

        $founders = $founders->paginate($this->per_page);

        return $this->response->withArray([
            'result' => [
                'success' => true,
                'data' => [
                    'founders' => $founders->items(),
                    'charter_capital' => $charter_capital,
                    'pagination' => [
                        'total' => $founders->total(),
                        'current_page' => $founders->currentPage(),
                    ],
                ],
            ]
        ]);
    }

    public function inventory() {
        $founders = $this->founder;

        if ($str = \request('search'))
        {
            $founders = $founders->search($str);
        }

        $founders = $founders->select([
            'founders.id AS id', 'users.name AS name'
        ]);

        $founders = $founders->join('users', function ($join) {
            $join->on('users.id', 'founders.user_id')
                ->whereNull('users.deleted_at');
        });

        $founders = $founders->sort()->paginate($this->per_page);

        return $this->response->withArray([
            'result' => [
                'success' => true,
                'data' => [
                    'founders' => $founders->items(),
                    'pagination' => [
                        'total' => $founders->total(),
                        'current_page' => $founders->currentPage(),
                    ],
                ],
            ],
        ]);
    }

    public function store(FounderRequest $request)
    {
        Founder::create([
            'user_id'   => $request['user_id'],
            'is_active' => $request['is_active'],
            'comment'   => $request['comment'],
        ]);


        return $this->response->withArray([
            'result' => [
                'success' => true,
                'message' => trans('messages.store_success',['name' => trans('messages.founder')]),
                'data'    => [],
            ]
        ]);
    }

    public function show(Founder $founder)
    {
        return $this->response->withArray([
            'result' => [
                'success' => true,
                'data'    => [
                    'founder'  => new \App\Http\Resources\Founder($founder, true)
                ]
            ]
        ]);
    }

    public function update(FounderRequest $request, Founder $founder)
    {
        $founder->update([
            'user_id'   => $request['user_id'],
            'is_active' => $request['is_active'],
            'comment'   => $request['comment'],
        ]);

        return $this->response->withArray([
            'result' => [
                'success' => true,
                'message' => trans('messages.update_success',['name' => trans('messages.founder')]),
                'data'    => [],
            ]
        ]);
    }

    public function destroy(Founder $founder)
    {
        $founder->delete();

        return $this->response->withArray([
            'result' => [
                'success' => true,
                'message' => trans('messages.destroy_success',['name' => trans('messages.founder')]),
            ]
        ]);
    }

    public function getSharePercentageOfFounder($founder_id)
    {
        if (!$founder = Founder::find($founder_id)) {
            throw new ApiModelNotFoundException($this->message_not_found);
        }

        $data = collect();

        // bundan oldin uchreditelni kirimiyam chiqimiyam bolmagan boladi
        $first_investment_of_founder = FounderOperation::where('founder_id', $founder_id)
            ->where('operation_type_id', FounderOperation::OPERATION_TYPE_INVESTMENT_ID)
            ->with('currency:id,name')
            ->orderBy('start_date', 'ASC')
            ->first();

        if ($first_investment_of_founder) {
            /**
             * vlojeniya kiritganda uchreditelli dolyasi qancha bolganini hisoblash
             */
            $total_all_investment = floatval(FounderOperation::where('operation_type_id', FounderOperation::OPERATION_TYPE_INVESTMENT_ID)
                ->where('start_date', '<=', Carbon::parse($first_investment_of_founder->start_date)->toDateTimeString())
                ->sum(DB::raw('total_price * rate')));

            $total_all_output_exit = floatval(FounderOperation::where('operation_type_id', FounderOperation::OPERATION_TYPE_OUTPUT_ID)
                ->where('output_type_id', FounderOperation::OUTPUT_TYPE_FOUNDER_EXIT_ID)
                ->where('start_date', '<=', Carbon::parse($first_investment_of_founder->start_date)->toDateTimeString())
                ->sum(DB::raw('total_price * rate')));

            // shu vlojeniyagacha bolgan ustavnoy fond
            $charter_capital = $total_all_investment - $total_all_output_exit;

            $current_share_percentage = ($charter_capital > 0) ? round((100 * ($first_investment_of_founder->total_price * $first_investment_of_founder->rate) / $charter_capital), 2) : 0;

            // Agar birinchi vlojeniyadan keyin yana operatsiya bolgan bolsa dolyasi osha voqtgacha boladi
            $next_operation_model = FounderOperation::where(function ($query) use($first_investment_of_founder) {
                    $query->where('operation_type_id', FounderOperation::OPERATION_TYPE_INVESTMENT_ID)
                        ->where('start_date', '>', Carbon::parse($first_investment_of_founder->start_date)->toDateTimeString());
                })->orWhere(function($query) use($first_investment_of_founder) {
                    $query->where('operation_type_id', FounderOperation::OPERATION_TYPE_OUTPUT_ID)
                        ->where('output_type_id', FounderOperation::OUTPUT_TYPE_FOUNDER_EXIT_ID)
                        ->where('start_date', '>', Carbon::parse($first_investment_of_founder->start_date)->toDateTimeString());
                })
                ->orderBy('start_date', 'ASC')
                ->first();

            $data->push([
                'is_first_operation'        => true,
                'operation_type'            => FounderOperation::getOperationTypeById($first_investment_of_founder->operation_type_id),
                'investment_type'           => FounderOperation::getInvestmentTypeById($first_investment_of_founder->investment_type_id),
                'start_date'                => date(Controller::ELEMENT_DATE_TIME_FORMAT, strtotime($first_investment_of_founder->start_date)),
                'end_date'                  => $next_operation_model ? (date(Controller::ELEMENT_DATE_TIME_FORMAT, strtotime($next_operation_model->start_date))) : '',
                'total_price'               => $first_investment_of_founder->total_price,
                'currency'                  => $first_investment_of_founder->currency,
                'rate'                      => $first_investment_of_founder->rate,
                'current_share_percentage'  => $current_share_percentage,
                'change_of_share_percentage'=> 0,
            ]);


            // 1-vlojeniyadan boshlab keyingi hamma kirdi va chiqdilarni olish
            $operations_from_first_investment = FounderOperation::where(function ($query) use($first_investment_of_founder) {
                    $query->where('operation_type_id', FounderOperation::OPERATION_TYPE_INVESTMENT_ID)
                        ->where('start_date', '>', Carbon::parse($first_investment_of_founder->start_date)->toDateTimeString());
                })->orWhere(function($query) use($first_investment_of_founder) {
                    $query->where('operation_type_id', FounderOperation::OPERATION_TYPE_OUTPUT_ID)
                        ->where('output_type_id', FounderOperation::OUTPUT_TYPE_FOUNDER_EXIT_ID)
                        ->where('start_date', '>', Carbon::parse($first_investment_of_founder->start_date)->toDateTimeString());
                })
                ->with('currency:id,name')
                ->orderBy('start_date', 'ASC')
                ->get();

            foreach ($operations_from_first_investment as $operation) {
                /**
                 * shu operatsiya qoshilganda dolyasi qancha bolganini topish
                 */
                $total_all_investment = floatval(FounderOperation::where('operation_type_id', FounderOperation::OPERATION_TYPE_INVESTMENT_ID)
                    ->where('start_date', '<=', Carbon::parse($operation->start_date)->toDateTimeString())
                    ->sum(DB::raw('total_price * rate')));

                $total_all_output_exit = floatval(FounderOperation::where('operation_type_id', FounderOperation::OPERATION_TYPE_OUTPUT_ID)
                    ->where('output_type_id', FounderOperation::OUTPUT_TYPE_FOUNDER_EXIT_ID)
                    ->where('start_date', '<=', Carbon::parse($operation->start_date)->toDateTimeString())
                    ->sum(DB::raw('total_price * rate')));

                // shu vlojeniyagacha bolgan ustavnoy fond
                $charter_capital = $total_all_investment - $total_all_output_exit;

                $total_all_investment_of_founder = floatval(FounderOperation::where('founder_id', $founder->id)
                    ->where('operation_type_id', FounderOperation::OPERATION_TYPE_INVESTMENT_ID)
                    ->where('start_date', '<=', Carbon::parse($operation->start_date)->toDateTimeString())
                    ->sum(DB::raw('total_price * rate')));

                $total_all_output_exit_of_founder = floatval(FounderOperation::where('founder_id', $founder->id)
                    ->where('operation_type_id', FounderOperation::OPERATION_TYPE_OUTPUT_ID)
                    ->where('output_type_id', FounderOperation::OUTPUT_TYPE_FOUNDER_EXIT_ID)
                    ->where('start_date', '<=', Carbon::parse($operation->start_date)->toDateTimeString())
                    ->sum(DB::raw('total_price * rate')));

                $current_share_percentage = ($charter_capital > 0) ? round((100 * ($total_all_investment_of_founder - $total_all_output_exit_of_founder) / $charter_capital), 2) : 0;


                // Agar bu operatsiyadan keyin yana operatsiya bolgan bolsa dolyasi osha voqtgacha boladi
                $next_operation_model = FounderOperation::where(function ($query) use($operation) {
                        $query->where('operation_type_id', FounderOperation::OPERATION_TYPE_INVESTMENT_ID)
                            ->where('start_date', '>', Carbon::parse($operation->start_date)->toDateTimeString());
                    })->orWhere(function($query) use($operation) {
                        $query->where('operation_type_id', FounderOperation::OPERATION_TYPE_OUTPUT_ID)
                            ->where('output_type_id', FounderOperation::OUTPUT_TYPE_FOUNDER_EXIT_ID)
                            ->where('start_date', '>', Carbon::parse($operation->start_date)->toDateTimeString());
                    })
                    ->orderBy('start_date', 'ASC')
                    ->first();

                // $data dan oxirgi itemni olib uni "current_share_percentage" olish. Hozirgi qoshiladigan qanchaga ozgarganini topish uchun
                $last_item_of_data = $data->last();
                $prev_share_percentage = 0;
                if ($last_item_of_data) {
                    $prev_share_percentage = $last_item_of_data['current_share_percentage'];
                }

                // agar uchreditelniy ozi operatsiya bajargan bolsa hamma malumotini chiqarish
                if ($operation->founder_id == $founder->id) {
                    $data->push([
                        'is_first_operation'        => false,
                        'operation_type'            => FounderOperation::getOperationTypeById($operation->operation_type_id),
                        'investment_type'           => FounderOperation::getInvestmentTypeById($first_investment_of_founder->investment_type_id),
                        'start_date'                => date(Controller::ELEMENT_DATE_TIME_FORMAT, strtotime($operation->start_date)),
                        'end_date'                  => $next_operation_model ? (date(Controller::ELEMENT_DATE_TIME_FORMAT, strtotime($next_operation_model->start_date))) : '',
                        'total_price'               => $operation->total_price,
                        'currency'                  => $operation->currency,
                        'rate'                      => $operation->rate,
                        'current_share_percentage'  => $current_share_percentage,
                        'change_of_share_percentage'=> round($current_share_percentage - $prev_share_percentage, 2),
                    ]);
                }
                else {
                    $data->push([
                        'is_first_operation'        => false,
                        'operation_type'            => null,
                        'investment_type'           => null,
                        'start_date'                => date(Controller::ELEMENT_DATE_TIME_FORMAT, strtotime($operation->start_date)),
                        'end_date'                  => $next_operation_model ? (date(Controller::ELEMENT_DATE_TIME_FORMAT, strtotime($next_operation_model->start_date))) : '',
                        'total_price'               => null,
                        'currency'                  => null,
                        'rate'                      => null,
                        'current_share_percentage'  => $current_share_percentage,
                        'change_of_share_percentage'=> round($current_share_percentage - $prev_share_percentage, 2),
                    ]);
                }
            }
        }

        return $this->response->withArray([
            'result' => [
                'success' => true,
                'operations'  => $data,
            ]
        ]);
    }
}
