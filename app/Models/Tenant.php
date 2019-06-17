<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use App\Models\{TenantHasOption, ApiUser, TenantLanguage};

class Tenant extends Model {

    use SoftDeletes;

	protected $table = 'tenant';

	protected $primaryKey = 'tenant_id';
	
    protected $fillable = ['name','sponsor_id'];

    protected $dates = ['created_at','updated_at','deleted_at'];
	
	protected $visible = ['tenant_id', 'name', 'sponsor_id', 'status', 'options', 'tenantLanguages', 'tenantLanguages.language'];
	
    public $rules = [
        // Validation rules
		'name' => 'required|unique:tenant,name,NULL,tenant_id,deleted_at,NULL',
		'sponsor_id'  => 'required',
    ];

    protected $softDelete = true;
    
    /*
    * Defined has many relation for the tenant_option table.
    */
    public function options()
    {
    	return $this->hasMany(TenantHasOption::class, 'tenant_id', 'tenant_id');
    }

    /*
    * Defined has many relation for the languages table.
    */
    public function tenantLanguages()
    {
        return $this->hasMany(TenantLanguage::class, 'tenant_id', 'tenant_id');
    }

    /*
    * Defined has many relation for the api_users table.
    */
    public function apiUsers()
    {
        return $this->hasMany(ApiUser::class, 'tenant_id', 'tenant_id');
    }
	
	public function getAll()
    {
        return static::with('options','tenantLanguages','tenantLanguages.language')->paginate(config('constants.PER_PAGE_LIMIT'));
    }


    public function findTenant(int $id)
    {
        return static::with('options','tenantLanguages','tenantLanguages.language')->findOrFail($id);
    }


    public function deleteTenant(int $id)
    {
        return static::findOrFail($id)->delete();
    }
}
