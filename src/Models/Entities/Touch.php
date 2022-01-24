<?php

namespace WalkerChiu\Blog\Models\Entities;

use WalkerChiu\Core\Models\Entities\Casts\BinaryIp;
use WalkerChiu\Core\Models\Entities\DateTrait;
use WalkerChiu\Core\Models\Entities\UuidModel;

class Touch extends UuidModel
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
        'user_id', 'article_id',
        'header',
        'ip'
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
     * The attributes that should be cast to native types.
     *
     * @var Array
     */
    protected $casts = [
        'header' => 'json',
        'ip'     => BinaryIp::class
    ];



    /**
     * Create a new instance.
     *
     * @param Array  $attributes
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        $this->table = config('wk-core.table.blog.touches');

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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function article()
    {
        return $this->belongsTo(config('wk-core.class.blog.article'), 'article_id', 'id');
    }
}
