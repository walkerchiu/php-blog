<?php

namespace WalkerChiu\Blog\Models\Repositories;

use Illuminate\Support\Facades\App;
use WalkerChiu\Core\Models\Forms\FormTrait;
use WalkerChiu\Core\Models\Repositories\Repository;
use WalkerChiu\Core\Models\Repositories\RepositoryTrait;
use WalkerChiu\Core\Models\Services\PackagingFactory;
use WalkerChiu\MorphImage\Models\Repositories\ImageRepositoryTrait;

class BlogRepository extends Repository
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
        $this->instance = App::make(config('wk-core.class.blog.blog'));
    }

    /**
     * @param String  $code
     * @param Array   $data
     * @param Bool    $is_enabled
     * @param Bool    $auto_packing
     * @return Array|Collection|Eloquent
     */
    public function list(string $code, array $data, $is_enabled = null, $auto_packing = false)
    {
        $instance = $this->instance;
        if ($is_enabled === true)      $instance = $instance->ofEnabled();
        elseif ($is_enabled === false) $instance = $instance->ofDisabled();

        $data = array_map('trim', $data);
        $repository = $instance->with(['langs' => function ($query) use ($code) {
                                    $query->ofCurrent()
                                          ->ofCode($code);
                                }])
                                ->whereHas('langs', function ($query) use ($code) {
                                    return $query->ofCurrent()
                                                 ->ofCode($code);
                                })
                                ->with(['tags'])
                                ->when($data, function ($query, $data) use ($is_enabled) {
                                    return $query->unless(empty($data['id']), function ($query) use ($data) {
                                                return $query->where('id', $data['id']);
                                            })
                                            ->unless(empty($data['user_id']), function ($query) use ($data) {
                                                return $query->where('user_id', $data['user_id']);
                                            })
                                            ->unless(empty($data['identifier']), function ($query) use ($data) {
                                                return $query->where('identifier', $data['identifier']);
                                            })
                                            ->unless(empty($data['language']), function ($query) use ($data) {
                                                return $query->where('language', $data['language']);
                                            })
                                            ->unless(empty($data['script_head']), function ($query) use ($data) {
                                                return $query->where('script_head', 'LIKE', "%".$data['script_head']."%");
                                            })
                                            ->unless(empty($data['script_footer']), function ($query) use ($data) {
                                                return $query->where('script_footer', 'LIKE', "%".$data['script_footer']."%");
                                            })
                                            ->when(isset($data['is_highlighted']), function ($query) use ($data) {
                                                return $query->where('is_highlighted', $data['is_highlighted']);
                                            })
                                            ->unless(empty($data['name']), function ($query) use ($data) {
                                                return $query->whereHas('langs', function ($query) use ($data) {
                                                    $query->ofCurrent()
                                                        ->where('key', 'name')
                                                        ->where('value', 'LIKE', "%".$data['name']."%");
                                                });
                                            })
                                            ->unless(empty($data['description']), function ($query) use ($data) {
                                                return $query->whereHas('langs', function ($query) use ($data) {
                                                    $query->ofCurrent()
                                                        ->where('key', 'description')
                                                        ->where('value', 'LIKE', "%".$data['description']."%");
                                                });
                                            })
                                            ->unless(empty($data['keywords']), function ($query) use ($data) {
                                                return $query->whereHas('langs', function ($query) use ($data) {
                                                    $query->ofCurrent()
                                                        ->where('key', 'keywords')
                                                        ->where('value', 'LIKE', "%".$data['keywords']."%");
                                                });
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
                                            });
                                })
                                ->orderBy('updated_at', 'DESC');

        if ($auto_packing) {
            $factory = new PackagingFactory(config('wk-blog.output_format'), config('wk-blog.pagination.pageName'), config('wk-blog.pagination.perPage'));
            $factory->setFieldsLang(['name', 'description', 'keywords']);
            return $factory->output($repository);
        }

        return $repository;
    }

    /**
     * @param Blog          $instance
     * @param String|Array  $code
     * @return Array
     */
    public function show($instance, $code): array
    {
    }
}
