<?php
namespace App\Models;

use Illuminate\Database\Eloquent\{Model, SoftDeletes};
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\FooterPagesLanguage;

class FooterPage extends Model
{
    use SoftDeletes;
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'footer_page';
    
    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'page_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['status', 'slug'];
    
    /**
     * The attributes that should be visible in arrays.
     *
     * @var array
     */
    protected $visible = ['page_id', 'status', 'slug', 'pageTranslations'];
    
    /**
     * Return the page's translations
     */
    public function pageTranslations(): HasMany
    {
        return $this->hasMany(FooterPagesLanguage::class, 'page_id', 'page_id');
    }
    
    /**
     * Soft delete the model from the database.
     *
     * @param  int  $id
     * @return void
     */
    public function deleteFooterPage(int $id)
    {
        return static::findOrFail($id)->delete();
    }
}
