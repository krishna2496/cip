<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TenantOption extends Model
{
    use SoftDeletes;
 
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tenant_option';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'tenant_option_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['option_name','option_value'];

    /**
     * The attributes that should be visible in arrays.
     *
     * @var array
     */
    protected $visible = ['option_name','option_value'];
    
    /**
     * Update resource.
     *
     * @param  array  $colorData
     * @return bool
     */
    public function addOrUpdateColor(array $colorData): bool
    {
        $styleData['option_name'] = $colorData['option_name'];
        $tenantOption = static::updateOrCreate($styleData);
        return $tenantOption->update(['option_value' => $colorData['option_value']]);
    }

    public function getOptionValueAttribute($value)
    {
        return (@json_decode($value) === false) ? $value : json_decode($value, true);
    }
}
