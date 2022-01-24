<?php

namespace WalkerChiu\Blog\Models\Entities;

trait UserTrait
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function blog()
    {
        return $this->hasOne(config('wk-core.class.blog.blog'),
                             'user_id',
                             'id');
    }
}
