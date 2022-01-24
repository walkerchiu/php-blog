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
        if ($entity->isForceDeleting()) {
            $entity->langs()->withTrashed()
                            ->forceDelete();
            $records = $entity->articles()->withTrashed()->get();
            foreach ($records as $record) {
                $record->forceDelete();
            }
            $entity->tags()->detach();

            if (
                config('wk-blog.onoff.firewall')
                && !empty(config('wk-core.class.firewall.firewall'))
            ) {
                $records = $entity->firewalls()->withTrashed()->get();
                foreach ($records as $record) {
                    $record->forceDelete();
                }
            }
            if (
                config('wk-blog.onoff.morph-address')
                && !empty(config('wk-core.class.morph-address.address'))
            ) {
                $records = $entity->addresses()->withTrashed()->get();
                foreach ($records as $record) {
                    $record->forceDelete();
                }
            }
            if (
                config('wk-blog.onoff.morph-board')
                && !empty(config('wk-core.class.morph-board.board'))
            ) {
                $records = $entity->boards()->withTrashed()->get();
                foreach ($records as $record) {
                    $record->forceDelete();
                }
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
                $records = $entity->images()->withTrashed()->get();
                foreach ($records as $record) {
                    $record->forceDelete();
                }
            }
            if (
                config('wk-blog.onoff.morph-registration')
                && !empty(config('wk-core.class.morph-registration.registration'))
            ) {
                $records = $entity->registrations()->withTrashed()->get();
                foreach ($records as $record) {
                    $record->forceDelete();
                }
            }
            if (
                config('wk-blog.onoff.morph-link')
                && !empty(config('wk-core.class.morph-link.link'))
            ) {
                $records = $entity->links()->withTrashed()->get();
                foreach ($records as $record) {
                    $record->forceDelete();
                }
            }
        }

        if (!config('wk-blog.soft_delete')) {
            $entity->forceDelete();
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
