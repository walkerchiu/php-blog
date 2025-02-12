<?php

namespace WalkerChiu\Blog;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use WalkerChiu\Blog\Models\Entities\Article;
use WalkerChiu\Blog\Models\Entities\ArticleLang;
use WalkerChiu\Blog\Models\Entities\Blog;

class ArticleTest extends \Orchestra\Testbench\TestCase
{
    use RefreshDatabase;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__ .'/../migrations');
        $this->withFactories(__DIR__ .'/../../src/database/factories');
    }

    /**
     * To load your package service provider, override the getPackageProviders.
     *
     * @param \Illuminate\Foundation\Application  $app
     * @return Array
     */
    protected function getPackageProviders($app)
    {
        return [\WalkerChiu\Core\CoreServiceProvider::class,
                \WalkerChiu\Blog\BlogServiceProvider::class];
    }

    /**
     * Define environment setup.
     *
     * @param \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
    }

    /**
     * A basic functional test on Article.
     *
     * For WalkerChiu\Blog\Models\Entities\Article
     *
     * @return void
     */
    public function testBlog()
    {
        // Config
        Config::set('wk-core.onoff.core-lang_core', 0);
        Config::set('wk-blog.onoff.core-lang_core', 0);
        Config::set('wk-core.lang_log', 1);
        Config::set('wk-blog.lang_log', 1);
        Config::set('wk-core.soft_delete', 1);
        Config::set('wk-blog.soft_delete', 1);

        // Give
        $db_blog = factory(Blog::class)->create();
        $db_record_1 = factory(Article::class)->create(['blog_id' => $db_blog->id]);
        $db_record_2 = factory(Article::class)->create(['blog_id' => $db_blog->id]);
        $db_record_3 = factory(Article::class)->create(['blog_id' => $db_blog->id, 'is_enabled' => 1]);

        // Get records after creation
            // When
            $records = Article::all();
            // Then
            $this->assertCount(3, $records);

        // Delete someone
            // When
            $db_record_2->delete();
            $records = Article::all();
            // Then
            $this->assertCount(2, $records);

        // Resotre someone
            // When
            Article::withTrashed()
                   ->find($db_record_2->id)
                   ->restore();
            $db_record_2 = Article::find($db_record_2->id);
            $records = Article::all();
            // Then
            $this->assertNotNull($db_record_2);
            $this->assertCount(3, $records);

        // Scope query on enabled records
            // When
            $records = Article::ofEnabled()
                              ->get();
            // Then
            $this->assertCount(1, $records);

        // Scope query on disabled records
            // When
            $records = Article::ofDisabled()
                              ->get();
            // Then
            $this->assertCount(2, $records);
    }
}
