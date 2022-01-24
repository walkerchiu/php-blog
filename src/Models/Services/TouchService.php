<?php

namespace WalkerChiu\Blog\Models\Services;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Request;
use WalkerChiu\Core\Models\Services\CheckExistTrait;
use WalkerChiu\Core\Models\Services\ClientIpTrait;

class TouchService
{
    use CheckExistTrait;
    use ClientIpTrait;

    protected $repository;



    /**
     * Create a new service instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->repository = App::make(config('wk-core.class.blog.touchRepository'));
    }

    /**
     * @param Article|String  $source
     * @param String          $user_id
     * @return Touch
     */
    public function touch($source, $user_id = null)
    {
        $article_id = is_string($source) ? $source : $source->id;

        $request = Request::instance();

        return $this->repository->save([
            'user_id'    => $user_id,
            'article_id' => $article_id,
            'header'     => $request->headers->all(),
            'ip'         => $this->getClientIp()
        ]);
    }

    /**
     * @param Article|String  $source
     * @param String          $user_id
     * @param String          $ip
     * @return Int
     */
    public function countTouches($source, $user_id = null, $ip = null): int
    {
        $article_id = is_string($source) ? $source : $source->id;

        $data = [
            'article_id' => $article_id
        ];

        if ($user_id)
            $data['user_id'] = $user_id;

        if ($ip)
            $data['ip'] = $ip;

        return $this->repository->whereByArray($data)
                                ->count();
    }
}
