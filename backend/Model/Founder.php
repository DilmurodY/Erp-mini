<?php

namespace App\Models\Founder;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\FunctionTrait;
use App\Http\Controllers\Traits\ScopeTrait;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class Founder extends Model implements Auditable
{
    use ScopeTrait, FunctionTrait;
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    const TABLE_NAME = 'founders';

    protected $casts = [
        'is_active'     => 'boolean',
        'created_at'    => 'datetime:' . Controller::ELEMENT_DATE_TIME_FORMAT,
        'updated_at'    => 'datetime:' . Controller::ELEMENT_DATE_TIME_FORMAT,
        'total_price'   => 'float',
        'share_percentage' => 'float',
    ];

    protected $fillable = [
        'user_id', 'is_active', 'comment'
    ];

    private $search_columns = [

    ];

    public function scopeFilter($query){
        if ($filter = request('id')){
            $query = $query->where('id',(int) $filter);
        }
        if ($filter = request('user_id')){
            $query = $query->where('user_id', $filter);
        }
        if (request()->get('is_active',null) != null){
            $filter = request()->get('is_active');

            $query = $query->where('is_active', $filter);
        }
        if ($filter = request('phone')) {
            $user_ids = User::query()->where('phone', 'like', '%' . $filter . '%')->pluck('id')->toArray();
            $query = $query->whereIn('user_id', $user_ids);
        }
        if ($filter = request('email')) {
            $user_ids = User::query()->where('email', 'like', '%' . $filter . '%')->pluck('id')->toArray();
            $query = $query->whereIn('user_id', $user_ids);
        }
        if ($filter = request('comment')){
            $query = $query->where('comment', 'ilike', '%' . $filter . '%');
        }
        if ($filter = request('created_at')){
            $query = $query->whereDate('founders.created_at','=',Carbon::parse($filter)->toDateString());
        }
        if ($filter = request('updated_at')){
            $query = $query->whereDate('founders.updated_at','=',Carbon::parse($filter)->toDateString());
        }

        return $query;
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function operations() {
        return $this->hasMany(FounderOperation::class, 'founder_id');
    }

    public function getTranslateModelName() {
        return __('messages.founder');
    }

    public function getAllTranslateColumnNames() {
        return [
            'id'                        => __('messages.id'),
            'user_id'                   => __('messages.user'),
            'created_at'                => __('messages.created_at'),
            'updated_at'                => __('messages.updated_at'),
            'deleted_at'                => __('messages.deleted_at'),
        ];
    }
}
