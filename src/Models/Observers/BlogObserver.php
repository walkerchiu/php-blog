<?php

namespace WalkerChiu\Blog\Models\Observers;

class BlogObserver
{
    /**
     * Handle the entity "retrieved" event.
     *
     * @param Entity  $entity
     * @return void
     */
    public function retrieved($entity)
    {
        //
    }

    /**
     * Handle the entity "creating" event.
     *
     * @param Entity  $entity
     * @return void
     */
    public function creating($entity)
    {
        //
    }

    /**
     * Handle the entity "created" event.
     *
     * @param Entity  $entity
     * @return void
     */
    public function created($entity)
    {
        //
    }

    /**
     * Handle the entity "updating" event.
     *
     * @param Entity  $entity
     * @return void
     */
    public function updating($entity)
    {
        //
    }

    /**
     * Handle the entity "updated" event.
     *
     * @param Entity  $entity
     * @return void
     */
    public function updated($entity)
    {
        //
    }

    /**
     * Handle the entity "saving" event.
     *
     * @param Entity  $entity
     * @return void
     */
    public function saving($entity)
    {
        if (
            config('wk-core.class.blog.blog')
                ::where('id', '<>', $entity->id)
                ->where('identifier', $entity->identifier)
                ->exists()
        )
            return false;
    }

    /**
     * Handle the entity "saved" event.
     *
     * @param Entity  $entity
     * @return void
     */
    public function saved($entity)
    {
        //
    }

    /**
     * Handle the entity "deleting" event.
     *
     * @param Entity  $entity
     * @return void
     */
    public function deleting($entity)
    {
        //
    }

    /**
     * Handle the entity "deleted" event.
     *
     * Its Lang will be automatically removed by database.
     *
     * @param Entity  $entity
     * @return void
     */
    public function deleted($entity)
    {
        if (!config('wk-blog.soft_delete')) {
            $entity->forceDelete();
        }

        if ($entity->isForceDeleting()) {
            $entity->langs()->withTrashed()
                            ->forceDelete();
            foreach ($entity->articles as $article) {
                $article->withTrashed()->forceDelete();
            }
            $entity->tags()->detach();

            if (
                config('wk-blog.onoff.firewall')
                && !empty(config('wk-core.class.firewall.firewall'))
            ) {
                $entity->firewalls()->withTrashed()->delete();
            }
            if (
                config('wk-blog.onoff.morph-address')
                && !empty(config('wk-core.class.morph-address.address'))
            ) {
                $entity->addresses()->withTrashed()->forceDelete();
            }
            if (
                config('wk-blog.onoff.morph-board')
                && !empty(config('wk-core.class.morph-board.board'))
            ) {
                $entity->boards()->withTrashed()->forceDelete();
            }
            if (
                config('wk-blog.onoff.morph-category')
                && !empty(config('wk-core.class.blog.category'))
            ) {
                $entity->categories()->detach();
            }
            if (
                config('wk-blog.onoff.morph-image')
                && !empty(config('wk-core.class.morph-image.image'))
            ) {
                $entity->images()->withTrashed()->forceDelete();
            }
            if (
                config('wk-blog.onoff.morph-registration')
                && !empty(config('wk-core.class.morph-registration.registration'))
            ) {
                $entity->registrations()->withTrashed()->forceDelete();
            }
            if (
                config('wk-blog.onoff.morph-link')
                && !empty(config('wk-core.class.morph-link.link'))
            ) {
                $entity->links()->withTrashed()->forceDelete();
            }
        }
    }

    /**
     * Handle the entity "restoring" event.
     *
     * @param Entity  $entity
     * @return void
     */
    public function restoring($entity)
    {
        if (
            config('wk-core.class.blog.blog')
                ::where('id', '<>', $entity->id)
                ->where('identifier', $entity->identifier)
                ->exists()
        )
            return false;
    }

    /**
     * Handle the entity "restored" event.
     *
     * @param Entity  $entity
     * @return void
     */
    public function restored($entity)
    {
        //
    }
}
