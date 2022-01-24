<?php

namespace WalkerChiu\Blog\Models\Entities;

use WalkerChiu\Core\Models\Entities\UuidModel;

class Tag extends UuidModel
{
    public $timestamps = false;

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
        'identifier'
    ];



    /**
     * Create a new instance.
     *
     * @param Array  $attributes
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        $this->table = config('wk-core.table.blog.tags');

        parent::__construct($attributes);
    }
}
