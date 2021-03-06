<?php
/**
 * Created by PhpStorm.
 * User: GitzJoey
 * Date: 1/28/2017
 * Time: 11:57 PM
 */

namespace App\Model\Accounting;

use App\Traits\StoreFilter;

use Auth;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Model\AccountingCash
 *
 * @property int $id
 * @property int $store_id
 * @property string $code
 * @property string $name
 * @property bool $is_default
 * @property string $status
 * @property int $created_by
 * @property int $updated_by
 * @property int $deleted_by
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 * @method static \Illuminate\Database\Query\Builder|\App\Model\AccountingCash whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Model\AccountingCash whereStoreId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Model\AccountingCash whereCode($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Model\AccountingCash whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Model\AccountingCash whereIsDefault($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Model\AccountingCash whereStatus($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Model\AccountingCash whereCreatedBy($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Model\AccountingCash whereUpdatedBy($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Model\AccountingCash whereDeletedBy($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Model\AccountingCash whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Model\AccountingCash whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Model\AccountingCash whereDeletedAt($value)
 * @mixin \Eloquent
 * @property-read mixed $code_and_name
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Model\Accounting\CapitalDeposit[] $accountingCapitalDeposits
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Model\Accounting\CapitalWithdrawal[] $accountingCapitalWithdrawals
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Model\Accounting\Cost[] $accountingCosts
 */
class CashAccount extends Model
{
    use SoftDeletes;

    use StoreFilter;

    protected $dates = ['deleted_at'];

    protected $table = 'acc_cash_account';

    protected $appends = ['codeAndName'];

    protected $fillable = [
        'store_id',
        'type',
        'code',
        'name',
        'is_default',
        'status',
    ];

    protected $hidden = [
        'created_by',
        'created_at',
        'updated_by',
        'updated_at',
        'deleted_by',
        'deleted_at',
    ];

    public function hId()
    {
        return HashIds::encode($this->attributes['id']);
    }

    public function getCodeAndNameAttribute()
    {
        return $this->attributes['name'].' ('.$this->attributes['code'].')';
    }

    public function accountingCapitalDeposits()
    {
        return $this->hasMany('App\Model\Accounting\CapitalDeposit', 'destination_acc_cash_id');
    }

    public function accountingCapitalWithdrawals()
    {
        return $this->hasMany('App\Model\Accounting\CapitalWithdrawal', 'source_acc_cash_id');
    }

    public function accountingCosts()
    {
        return $this->hasMany('App\Model\Accounting\Cost', 'source_acc_cash_id');
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $user = Auth::user();
            if ($user) {
                $model->created_by = $user->id;
                $model->updated_by = $user->id;
            }
        });

        static::updating(function ($model) {
            $user = Auth::user();
            if ($user) {
                $model->updated_by = $user->id;
            }
        });

        static::deleting(function ($model) {
            $user = Auth::user();
            if ($user) {
                $model->deleted_by = $user->id;
                $model->save();
            }
        });
    }
}