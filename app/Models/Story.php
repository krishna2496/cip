<?php
namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Story extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'story';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'story_id';

    /**
     * The attributes that should be visible in arrays.
     *
     * @var array
     */

    protected $visible = ['story_id', 'user_id', 'mission_id', 'title', 'description', 'status', 'published_at',
        'mission_title', 'mission_description', 'mission_short_description', 'first_name', 'last_name',
        'linked_in_url', 'avatar', 'why_i_volunteer', 'profile_text', 'storyMedia', 'city', 'country', 'created_at',
        'translations', 'languages'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id', 'mission_id', 'title', 'description', 'status', 'published_at'];

    /**
     * Defined has one relation for the user table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'user_id', 'user_id');
    }

    /**
     * Defined has one relation for the mission table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function mission(): HasOne
    {
        return $this->hasOne(Mission::class, 'mission_id', 'mission_id');
    }

    /**
     * Get the media record associated with the story.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function storyMedia(): HasMany
    {
        return $this->hasMany(StoryMedia::class, 'story_id', 'story_id');
    }

    /**
     * Soft delete the model from the database.
     *
     * @param int $storyId
     * @param int $userId
     * @return bool
     */
    public function deleteStory(int $storyId, int $userId): bool
    {
        return static::where(['story_id' => $storyId,
        'user_id' => $userId])->firstOrFail()->delete();
    }
    
    /**
     * Get story title
     *
     * @param int $storyId
     * @return string
     */
    public function getStoryName(int $storyId): string
    {
        return static::select('title')->where(['story_id' => $storyId])->value('title');
    }

    /**
     * Remove the script tag from description attribute
     *
     * @param string $value
     * @return void
     */
    public function setDescriptionAttribute($value)
    {
        $this->attributes['description'] = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $value);
    }
}
