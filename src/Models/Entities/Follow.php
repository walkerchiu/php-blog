<?php

namespace WalkerChiu\Blog\Models\Entities;

use WalkerChiu\Core\Models\Entities\DateTrait;
use WalkerChiu\Core\Models\Entities\UuidModel;

class Follow extends UuidModel
{
    use DateTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var Array
     */
    /**
     * The attributes that are mass assignable.
     *
     * @var Array
     */
    protected $fillable = [
        'user_id',
        'morph_type', 'morph_id'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var Array
     */
    protected $dates = [
        'created_at',
        'updated_at'
    ];



    /**
     * Create a new instance.
     *
     * @param Array  $attributes
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        $this->table = config('wk-core.table.blog.follows');

        parent::__construct($attributes);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(config('wk-core.class.user'), 'user_id', 'id');
    }

    /**
     * Get the owning morph model.
     */
    public function morph()
    {
        return $this->morphTo();
    }
}
