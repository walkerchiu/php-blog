<?php

namespace WalkerChiu\Blog\Models\Entities;

use WalkerChiu\Core\Models\Entities\UuidEntity;
use WalkerChiu\Blog\Models\Entities\TagTrait;
use WalkerChiu\MorphImage\Models\Entities\ImageTrait;

class Article extends UuidEntity
{
    use ImageTrait;
    use TagTrait;



    /**
     * Create a new instance.
     *
     * @param Array  $attributes
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        $this->table = config('wk-core.table.blog.articles');

        $this->fillable = array_merge($this->fillable, [
            'blog_id',
            'newest_id',
            'identifier',
            'title', 'description', 'content',
            'cover', 'keywords',
            'can_comment', 'can_search',
            'is_highlighted',
            'edit_at'
        ]);

        $this->casts = array_merge($this->casts, [
            'can_comment'    => 'boolean',
            'can_search'     => 'boolean',
            'is_highlighted' => 'boolean',
            'edit_at'        => 'datetime'
        ]);

        parent::__construct($attributes);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function history()
    {
        return $this->hasMany(config('wk-core.class.blog.article'), 'newest_id', 'id')
                    ->whereNotNull('newest_id')
                    ->orderBy('edit_at', 'ASC');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function newest()
    {
        return $this->hasOne(config('wk-core.class.blog.article'), 'id', 'newest_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function blog()
    {
        return $this->belongsTo(config('wk-core.class.blog.blog'), 'blog_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function firewalls()
    {
        return $this->morphMany(config('wk-core.class.firewall.setting'), 'morph');
    }

    /**
     * @param String  $user_id
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function comments($user_id = null)
    {
        return $this->morphMany(config('wk-core.class.morph-comment.comment'), 'morph')
                    ->when($user_id, function ($query, $user_id) {
                                return $query->where('user_id', $user_id);
                            });
    }

    /**
     * Get all of the categories for the group.
     *
     * @param String  $type
     * @param Bool    $is_enabled
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function categories($type = null, $is_enabled = null)
    {
        $table = config('wk-core.table.morph-category.categories_morphs');
        return $this->morphToMany(config('wk-core.class.morph-category.category'), 'morph', $table)
                    ->when(is_null($type), function ($query) {
                          return $query->whereNull('type');
                      }, function ($query) use ($type) {
                          return $query->where('type', $type);
                      })
                    ->unless( is_null($is_enabled), function ($query) use ($is_enabled) {
                        return $query->where('is_enabled', $is_enabled);
                    });
    }

    /**
     * Get parent category.
     *
     * @param String  $type
     * @param Bool    $is_enabled
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function category($type = null, $is_enabled = null)
    {
        return $this->categories($type, $is_enabled)->first();
    }

    /**
     * @param String  $type
     * @return \Carbon\Carbon
     */
    public function timeago(string $type)
    {
        if ($type == 'created_at')
            return $this->created_at->diffForHumans();
        if ($type == 'updated_at')
            return $this->updated_at->diffForHumans();
        if ($type == 'deleted_at')
            return $this->updated_at->diffForHumans();
    }

    /**
     * Check if it belongs to the user.
     * 
     * @param User  $user
     * @return Bool
     */
    public function isOwnedBy($user): bool
    {
        if (empty($user))
            return false;

        return $this->blog->user_id == $user->id;
    }
}
