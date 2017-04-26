<?php
namespace App\Models\Paytrust;

use App\Models\Corporate\Customer as CustomerCorporation;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

use \App\Models\Paytrust\Role;
use \App\Models\Paytrust\CustomerCreditIncome as CreditIncome;
use Auth;

class User extends Model implements AuthenticatableContract, CanResetPasswordContract
{
    use Authenticatable, CanResetPassword;

    protected $table = 'enigma_peyton.sys_user';
    protected $primaryKey = 'id';

    public $permissions = [];
    public $timestamps = false;

    public $defaultRoutes = [
        'corporate.auth.login',
        'corporate.auth.logout'
    ];

    public function corporation()
    {
        return $this->belongsTo(CustomerCorporation::class);
    }

    /**
     * Get customer's allowed route list
     * @return \Illuminate\Support\Collection|static
     */
    public function role_access()
    {
        if ($results = RoleAccess::where(['role_id' => $this->role_id])->select(['route_name'])->get()) {
            return $results->pluck(['route_name']);
        }
        return collect([]);
    }

    public function permissions()
    {
        $role_id = Auth::get()->role_id;
        foreach (Role::where(['role_id' => $role_id])->get() as $key => $value) {
            if (!empty($value->section) && !empty($value->action)) {
                $this->permissions[] = $value->section . '.' . $value->action;
            }
        }
    }

    public function permission_check($section)
    {
        if (empty($this->permissions))
          $this->permissions();

        return in_array($section, $this->permissions);
    }

    public function balance()
    {
        return CreditIncome::getBalanceSum( $this->customer_id );
    }
    
    public function group()
    {
        return $this->belongsTo('App\Models\Corporate\Group','group_id','id');
    }

}
