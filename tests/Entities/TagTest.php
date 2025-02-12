<?php

namespace WalkerChiu\Blog;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use WalkerChiu\Blog\Models\Entities\Tag;
use WalkerChiu\Blog\Models\Entities\TagLang;

class TagTest extends \Orchestra\Testbench\TestCase
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
     * A basic functional test on Tag.
     *
     * For WalkerChiu\MorphTag\Models\Entities\Tag
     * 
     * @return void
     */
    public function testMorphTag()
    {
        // Config
        Config::set('wk-core.onoff.core-lang_core', 0);
        Config::set('wk-blog.onoff.core-lang_core', 0);
        Config::set('wk-core.lang_log', 1);
        Config::set('wk-blog.lang_log', 1);
        Config::set('wk-core.soft_delete', 1);
        Config::set('wk-blog.soft_delete', 1);

        // Give
        $db_record_1 = factory(Tag::class)->create();
        $db_record_2 = factory(Tag::class)->create();

        // Get records after creation
            // When
            $records = Tag::all();
            // Then
            $this->assertCount(2, $records);

        // Delete someone
            // When
            $db_record_2->delete();
            $records = Tag::all();
            // Then
            $this->assertCount(1, $records);
    }
}
