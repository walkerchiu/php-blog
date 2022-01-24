<?php

namespace WalkerChiu\Blog\Models\Entities;

use WalkerChiu\Core\Models\Entities\Lang;

class BlogLang extends Lang
{
    /**
     * Create a new instance.
     *
     * @param Array  $attributes
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        $this->table = config('wk-core.table.blog.blogs_lang');

        parent::__construct($attributes);
    }
}
