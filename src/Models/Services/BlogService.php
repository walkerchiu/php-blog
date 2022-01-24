<?php

namespace WalkerChiu\Blog\Models\Services;

use Illuminate\Support\Facades\App;
use WalkerChiu\Blog\Models\Services\LikeService;
use WalkerChiu\Blog\Models\Services\TouchService;
use WalkerChiu\Core\Models\Services\CheckExistTrait;

class BlogService
{
    use CheckExistTrait;

    protected $repository;



    /**
     * Create a new service instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->repository = App::make(config('wk-core.class.blog.blogRepository'));
    }

    /**
     * @param Mixed  $object
     * @param Bool   $isOwner
     * @return Bool
     */
    public function checkPathIsEnabled($object, bool $isOwner): bool
    {
        $flag = true;
        do {
            if ( is_a($object, config('wk-core.class.blog.article')) ) {
                $parent = $object->category();
                if (empty($parent))
                    $parent = $object->blog;
            } elseif ( is_a($object, config('wk-core.class.morph-category.category')) ) {
                $parent = $object->parent();
            } elseif ( is_a($object, config('wk-core.class.morph-comment.comment')) ) {
                $parent = $object->morph();
            } else {
                return $flag;
            }

            if (
                !$isOwner
                && !$parent->is_enabled
            ) {
                $flag = false;
                break;
            }

            $object = $parent;

        } while ( !is_a($parent, config('wk-core.class.blog.blog')) );

        return $flag;
    }

    /**
     * @param Blog    $blog
     * @param String  $user_id
     * @param String  $ip
     * @return Int
     */
    public function countTotalTouches($blog, $user_id = null, $ip = null): int
    {
        $service = new TouchService();

        $nums = 0;
        foreach ($blog->articles as $article) {
            $nums += $service->countTouches($article->id, $user_id, $ip);
        }

        return $nums;
    }

    /**
     * @param Blog    $blog
     * @param String  $user_id
     * @return Int
     */
    public function countTotalLikes($blog, $user_id = null): int
    {
        $service = new LikeService();

        $nums = 0;
        foreach ($blog->articles as $article) {
            $nums += $service->countLikes($article, $user_id);
        }

        return $nums;
    }
}
