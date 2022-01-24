<?php

namespace WalkerChiu\Blog\Models\Entities;

use WalkerChiu\Core\Models\Entities\LangTrait;
use WalkerChiu\Core\Models\Entities\UuidEntity;
use WalkerChiu\Blog\Models\Entities\TagTrait;
use WalkerChiu\MorphImage\Models\Entities\ImageTrait;
use WalkerChiu\MorphRegistration\Models\Entities\RegistrationTrait;

class Blog extends UuidEntity
{
    use LangTrait;
    use ImageTrait;
    use RegistrationTrait;
    use TagTrait;



    /**
     * Create a new instance.
     *
     * @param Array  $attributes
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        $this->table = config('wk-core.table.blog.blogs');

        $this->fillable = array_merge($this->fillable, [
            'user_id',
            'identifier',
            'language',
            'script_head', 'script_footer',
            'options',
            'is_highlighted'
        ]);

        $this->casts = array_merge($this->casts, [
            'is_highlighted' => 'boolean'
        ]);

        parent::__construct($attributes);
    }

    /**
     * Get it's lang entity.
     *
     * @return Lang
     */
    public function lang()
    {
        if (
            config('wk-core.onoff.core-lang_core')
            || config('wk-blog.onoff.core-lang_core')
        ) {
            return config('wk-core.class.core.langCore');
        } else {
            return config('wk-core.class.blog.blogLang');
        }
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function langs()
    {
        if (
            config('wk-core.onoff.core-lang_core')
            || config('wk-blog.onoff.core-lang_core')
        ) {
            return $this->langsCore();
        } else {
            return $this->hasMany(config('wk-core.class.blog.blogLang'), 'morph_id', 'id');
        }
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(config('wk-core.class.user'), 'user_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function articles()
    {
        return $this->hasMany(config('wk-core.class.blog.article'), 'blog_id', 'id');
    }

    /**
     * @param String  $type
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function addresses($type = null)
    {
        return $this->morphMany(config('wk-core.class.morph-address.address'), 'morph')
                    ->when($type, function ($query, $type) {
                                return $query->where('type', $type);
                            });
    }

    /**
     * @param String  $type
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function boards($type = null)
    {
        return $this->morphMany(config('wk-core.class.morph-board.board'), 'host')
                    ->when($type, function ($query, $type) {
                                return $query->where('type', $type);
                            });
    }

    /**
     * Get all of the categories for the blog.
     *
     * @param String  $type
     * @param Bool    $is_enabled
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function categories($type = null, $is_enabled = null)
    {
        return $this->morphMany(config('wk-core.class.morph-category.category'), 'host')
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
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function firewalls()
    {
        return $this->morphMany(config('wk-core.class.firewall.setting'), 'morph');
    }

    /**
     * @param String  $type
     * @param String  $category
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function links($type = null, $category = null)
    {
        return $this->morphMany(config('wk-core.class.morph-link.link'), 'morph')
                    ->when($type, function ($query, $type) {
                                return $query->where('type', $type);
                            })
                    ->when($category, function ($query, $category) {
                                return $query->where('category', $category);
                            });
    }

    /**
     * Get all of the navs for the site.
     *
     * @param String  $type
     * @param Bool    $is_enabled
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function navs($type = null, $is_enabled = null)
    {
        return $this->morphMany(config('wk-core.class.morph-nav.nav'), 'host')
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
     * Check if it belongs to the user.
     * 
     * @param User  $user
     * @return Bool
     */
    public function isOwnedBy($user): bool
    {
        if (empty($user))
            return false;

        return $this->user_id == $user->id;
    }
}
