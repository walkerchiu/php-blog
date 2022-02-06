<?php

namespace WalkerChiu\Blog;

use Illuminate\Support\ServiceProvider;

class BlogServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfig();
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Publish config files
        $this->publishes([
           __DIR__ .'/config/blog.php' => config_path('wk-blog.php'),
        ], 'config');

        // Publish migration files
        $from = __DIR__ .'/database/migrations/';
        $to   = database_path('migrations') .'/';
        $this->publishes([
            $from .'create_wk_blog_table.php'
                => $to .date('Y_m_d_His', time()) .'_create_wk_blog_table.php',
        ], 'migrations');

        $this->loadTranslationsFrom(__DIR__.'/translations', 'php-blog');
        $this->publishes([
            __DIR__.'/translations' => resource_path('lang/vendor/php-blog'),
        ]);

        if ($this->app->runningInConsole()) {
            $this->commands([
                config('wk-blog.command.cleaner')
            ]);
        }

        config('wk-core.class.blog.article')::observe(config('wk-core.class.blog.articleObserver'));
        config('wk-core.class.blog.blog')::observe(config('wk-core.class.blog.blogObserver'));
        config('wk-core.class.blog.blogLang')::observe(config('wk-core.class.blog.blogLangObserver'));
        config('wk-core.class.blog.follow')::observe(config('wk-core.class.blog.followObserver'));
        config('wk-core.class.blog.like')::observe(config('wk-core.class.blog.likeObserver'));
        config('wk-core.class.blog.touch')::observe(config('wk-core.class.blog.touchObserver'));
    }

    /**
     * Merges user's and package's configs.
     *
     * @return void
     */
    private function mergeConfig()
    {
        if (!config()->has('wk-blog')) {
            $this->mergeConfigFrom(
                __DIR__ .'/config/blog.php', 'wk-blog'
            );
        }

        $this->mergeConfigFrom(
            __DIR__ .'/config/blog.php', 'blog'
        );
    }

    /**
     * Merge the given configuration with the existing configuration.
     *
     * @param String  $path
     * @param String  $key
     * @return void
     */
    protected function mergeConfigFrom($path, $key)
    {
        if (
            !(
                $this->app instanceof CachesConfiguration
                && $this->app->configurationIsCached()
            )
        ) {
            $config = $this->app->make('config');
            $content = $config->get($key, []);

            $config->set($key, array_merge(
                require $path, $content
            ));
        }
    }
}
