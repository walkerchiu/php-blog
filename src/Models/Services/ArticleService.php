<?php

namespace WalkerChiu\Blog\Models\Services;

use Illuminate\Support\Facades\App;
use WalkerChiu\Core\Models\Services\CheckExistTrait;
use WalkerChiu\Blog\Models\Services\BlogService;

class ArticleService
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
        $this->repository = App::make(config('wk-core.class.blog.articleRepository'));
    }

    /**
     * @param Article  $article
     * @param String   $type
     * @param Bool     $isOwner
     * @return Article
     */
    public function getSiblingArticle($article, string $type, $isOwner = false)
    {
        switch ($type) {
            case "prev":
                $operator  = '<';
                $orderType = 'DESC';
                break;
            case "next":
                $operator  = '>';
                $orderType = 'ASC';
                break;
            default:
                return $article;
        }

        $category = $article->category(null, $isOwner);
        $records = $this->repository->whereNull('newest_id')
                                    ->where('id', '<>', $article->id)
                                    ->unless($isOwner, function ($query) {
                                        return $query->ofEnabled();
                                    })
                                    ->unless(empty($category), function ($query) use ($category, $isOwner) {
                                        return $query->whereHas('categories', function ($query) use ($category, $isOwner) {
                                            $query->unless($isOwner, function ($query) {
                                                        return $query->ofEnabled();
                                                    })
                                                    ->where('id', '=', $category->id);
                                        });
                                    })
                                    ->where('created_at', $operator, $article->created_at)
                                    ->orderBy('created_at', $orderType)
                                    ->get();

        $service = new BlogService();

        foreach ($records as $record) {
            $category = $record->category(null, $isOwner);
            if (empty($category)) {
                return $record;
            } elseif (
                !$isOwner &&
                !$category->is_enabled
            ) {
                continue;
            } elseif ($service->checkPathIsEnabled($category, $isOwner)) {
                return $record;
            }
        }

        return;
    }

    /**
     * @param String  $article_id
     * @param String  $owner_user_id
     * @param String  $viewer_user_id
     * @return Int
     */
    public function countComments(string $article_id, string $owner_user_id, $viewer_user_id = null): int
    {
        $data = [
            'morph_type' => config('wk-core.class.blog.article'),
            'morph_id'   => $article_id
        ];

        if ($owner_user_id != $viewer_user_id) {
            $data['is_enabled'] = 1;

            return App::make(config('wk-core.class.morph-comment.commentRepository'))
                        ->whereByArray($data)
                        ->when($viewer_user_id, function ($query) use ($data, $viewer_user_id) {
                            return $query->orWhere( function ($query) use ($data, $viewer_user_id) {
                                return $query->where('morph_type', $data['morph_type'])
                                            ->where('morph_id', $data['morph_id'])
                                            ->where('is_enabled', $data['is_enabled'])
                                            ->where('user_id', $viewer_user_id);
                            });
                        })
                        ->count();
        }

        return App::make(config('wk-core.class.morph-comment.commentRepository'))
                    ->whereByArray($data)
                    ->count();
    }
}
