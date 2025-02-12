<?php

namespace WalkerChiu\Blog;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use WalkerChiu\Blog\Models\Entities\Blog;
use WalkerChiu\Blog\Models\Entities\BlogLang;

class BlogTest extends \Orchestra\Testbench\TestCase
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
     * A basic functional test on Blog.
     *
     * For WalkerChiu\Blog\Models\Entities\Blog
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
        $db_record_1 = factory(Blog::class)->create();
        $db_record_2 = factory(Blog::class)->create();
        $db_record_3 = factory(Blog::class)->create(['is_enabled' => 1]);

        // Get records after creation
            // When
            $records = Blog::all();
            // Then
            $this->assertCount(3, $records);

        // Delete someone
            // When
            $db_record_2->delete();
            $records = Blog::all();
            // Then
            $this->assertCount(2, $records);

        // Resotre someone
            // When
            Blog::withTrashed()
                ->find($db_record_2->id)
                ->restore();
            $db_record_2 = Blog::find($db_record_2->id);
            $records = Blog::all();
            // Then
            $this->assertNotNull($db_record_2);
            $this->assertCount(3, $records);

        // Return Lang class
            // When
            $class = $db_record_2->lang();
            // Then
            $this->assertEquals($class, BlogLang::class);

        // Scope query on enabled records
            // When
            $records = Blog::ofEnabled()
                           ->get();
            // Then
            $this->assertCount(1, $records);

        // Scope query on disabled records
            // When
            $records = Blog::ofDisabled()
                           ->get();
            // Then
            $this->assertCount(2, $records);
    }
}
