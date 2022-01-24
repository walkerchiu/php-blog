<?php

namespace WalkerChiu\Blog\Models\Services;

use Illuminate\Support\Facades\App;
use WalkerChiu\Core\Models\Services\CheckExistTrait;

class FollowService
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
        $this->repository = App::make(config('wk-core.class.blog.followRepository'));
    }

    /**
     * @param Entity  $entity
     * @param User    $user
     * @return Follow
     */
    public function follow($entity, $user)
    {
        if (empty($user))
            return null;

        $data = [
            'user_id'    => $user->id,
            'morph_type' => get_class($entity),
            'morph_id'   => $entity->id
        ];

        if (
            !$this->repository->whereByArray($data)
                              ->exists()
        ) {
            return $this->repository->save($data);
        }

        return null;
    }

    /**
     * @param Entity  $entity
     * @param User    $user
     * @return Follow
     */
    public function unFollow($entity, $user)
    {
        if (empty($user))
            return null;

        $data = [
            'user_id'    => $user->id,
            'morph_type' => get_class($entity),
            'morph_id'   => $entity->id
        ];

        if (
            !$this->repository->whereByArray($data)
                              ->exists()
        ) {
            return $this->repository->save($data);
        }

        return null;
    }

    /**
     * @param Entity  $entity
     * @param String  $user_id
     * @return Int
     */
    public function countFollows($entity, $user_id = null): int
    {
        $data = [
            'morph_type' => get_class($entity),
            'morph_id'   => $entity->id
        ];

        if ($user_id)
            $data['user_id'] = $user_id;

        return $this->repository->whereByArray($data)
                                ->count();
    }
}
