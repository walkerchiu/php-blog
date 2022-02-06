<?php

namespace WalkerChiu\Blog\Models\Repositories;

use Illuminate\Support\Facades\App;
use WalkerChiu\Core\Models\Forms\FormTrait;
use WalkerChiu\Core\Models\Repositories\Repository;
use WalkerChiu\Core\Models\Repositories\RepositoryTrait;
use WalkerChiu\Core\Models\Services\PackagingFactory;
use WalkerChiu\MorphImage\Models\Repositories\ImageRepositoryTrait;

class ArticleRepository extends Repository
{
    use FormTrait;
    use RepositoryTrait;
    use ImageRepositoryTrait;

    protected $instance;



    /**
     * Create a new repository instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->instance = App::make(config('wk-core.class.blog.article'));
    }

    /**
     * @param Array  $data
     * @param Bool   $is_enabled
     * @param Bool   $auto_packing
     * @return Array|Collection|Eloquent
     */
    public function list(array $data, $is_enabled = null, $auto_packing = false)
    {
        $instance = $this->instance;
        if ($is_enabled === true)      $instance = $instance->ofEnabled();
        elseif ($is_enabled === false) $instance = $instance->ofDisabled();

        $repository = $instance->with(['tags'])
                                ->when($data, function ($query, $data) use ($is_enabled) {
                                    return $query->unless(empty($data['id']), function ($query) use ($data) {
                                                return $query->where('id', $data['id']);
                                            })
                                            ->unless(empty($data['blog_id']), function ($query) use ($data) {
                                                return $query->where('blog_id', $data['blog_id']);
                                            })
                                            ->when(
                                                isset($data['onlyNewest'])
                                                && $data['onlyNewest']
                                            , function ($query) {
                                                return $query->whereNull('newest_id');
                                            }, function ($query) use ($data) {
                                                return $query->where('newest_id', $data['newest_id']);
                                            })
                                            ->unless(empty($data['identifier']), function ($query) use ($data) {
                                                return $query->where('identifier', $data['identifier']);
                                            })
                                            ->when(isset($data['can_comment']), function ($query) use ($data) {
                                                return $query->where('can_comment', $data['can_comment']);
                                            })
                                            ->when(isset($data['can_search']), function ($query) use ($data) {
                                                return $query->where('can_search', $data['can_search']);
                                            })
                                            ->when(isset($data['is_highlighted']), function ($query) use ($data) {
                                                return $query->where('is_highlighted', $data['is_highlighted']);
                                            })
                                            ->unless(empty($data['edit_at']), function ($query) use ($data) {
                                                return $query->where('edit_at', $data['edit_at']);
                                            })
                                            ->unless(empty($data['title']), function ($query) use ($data) {
                                                return $query->where('title', 'LIKE', "%".$data['title']."%");
                                            })
                                            ->unless(empty($data['description']), function ($query) use ($data) {
                                                return $query->where('description', 'LIKE', "%".$data['description']."%");
                                            })
                                            ->unless(empty($data['content']), function ($query) use ($data) {
                                                return $query->where('content', 'LIKE', "%".$data['content']."%");
                                            })
                                            ->unless(empty($data['keywords']), function ($query) use ($data) {
                                                return $query->where('keywords', 'LIKE', "%".$data['keywords']."%");
                                            })
                                            ->unless(empty($data['categories']), function ($query) use ($data, $is_enabled) {
                                                return $query->whereHas('categories', function ($query) use ($data, $is_enabled) {
                                                    if ($is_enabled)
                                                        $query->ofEnabled()
                                                            ->whereIn('id', $data['categories']);
                                                    else
                                                        $query->whereIn('id', $data['categories']);
                                                });
                                            })
                                            ->unless(empty($data['tags']), function ($query) use ($data) {
                                                return $query->whereHas('tags', function ($query) use ($data) {
                                                    $query->whereIn('identifier', $data['tags']);
                                                });
                                            })
                                            ->unless(
                                                !empty($data['orderBy'])
                                                && !empty($data['orderType'])
                                            , function ($query) use ($data) {
                                                return $query->orderBy($data['orderBy'], $data['orderType']);
                                            }, function ($query) {
                                                return $query->orderBy('edit_at', 'DESC');
                                            });
                                }, function ($query) {
                                    return $query->orderBy('edit_at', 'DESC');
                                });

        if ($auto_packing) {
            $factory = new PackagingFactory(config('wk-blog.output_format'), config('wk-blog.pagination.pageName'), config('wk-blog.pagination.perPage'));
            return $factory->output($repository);
        }

        return $repository;
    }

    /**
     * @param Article  $instance
     * @return Array
     */
    public function show($instance): array
    {
    }

    /**
     * @param String  $value
     * @param Int     $count
     * @return Array
     */
    public function autoCompleteTitleOfEnabled($value, $count = 10): array
    {
        $records = $this->instance->with('blog')
                                ->ofEnabled()
                                ->whereHas('blog', function ($query) {
                                        $query->ofEnabled();
                                    })
                                ->where('title', 'LIKE', $value .'%')
                                ->orderBy('updated_at', 'DESC')
                                ->select('id', 'identifier', 'title')
                                ->take($count)
                                ->get();
        $list = [];
        foreach ($records as $record) {
            $list[] = ['id'         => $record->id,
                       'identifier' => $record->identifier,
                       'title'      => $record->title];
        }

        return $list;
    }
}
