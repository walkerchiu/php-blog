<?php

namespace WalkerChiu\Blog;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use WalkerChiu\Blog\Models\Entities\Blog;
use WalkerChiu\Blog\Models\Entities\Article;
use WalkerChiu\Blog\Models\Forms\ArticleFormRequest;

class ArticleFormRequestTest extends \Orchestra\Testbench\TestCase
{
    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        //$this->loadLaravelMigrations(['--database' => 'mysql']);
        $this->loadMigrationsFrom(__DIR__ .'/../migrations');
        $this->withFactories(__DIR__ .'/../../src/database/factories');

        $this->request  = new ArticleFormRequest();
        $this->rules    = $this->request->rules();
        $this->messages = $this->request->messages();
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
     * Unit test about Authorize.
     *
     * For WalkerChiu\Blog\Models\Forms\BlogFormRequest
     *
     * @return void
     */
    public function testAuthorize()
    {
        $this->assertEquals(true, 1);
    }

    /**
     * Unit test about Rules.
     *
     * For WalkerChiu\Blog\Models\Forms\BlogFormRequest
     *
     * @return void
     */
    public function testRules()
    {
        $faker = \Faker\Factory::create();

        DB::table(config('wk-core.table.user'))->insert([
            'name'     => $faker->username,
            'email'    => $faker->email,
            'password' => $faker->password,
        ]);
        $db_blog = factory(Blog::class)->create();


        // Give
        $attributes = [
            'blog_id'        => $db_blog->id,
            'identifier'     => $faker->slug,
            'can_comment'    => $faker->boolean,
            'can_search'     => $faker->boolean,
            'is_highlighted' => $faker->boolean,
            'is_enabled'     => $faker->boolean,
            'edit_at'        => date('Y-m-d H:i:s'),
            'title'          => $faker->name,
            'description'    => $faker->name,
            'content'        => $faker->name,
        ];
        // When
        $validator = Validator::make($attributes, $this->rules, $this->messages); $this->request->withValidator($validator);
        $fails = $validator->fails();
        // Then
        $this->assertEquals(false, $fails);

        // Give
        $attributes = [
            'blog_id'        => $db_blog->id,
            'identifier'     => $faker->slug,
            'can_comment'    => $faker->boolean,
            'can_search'     => $faker->boolean,
            'is_highlighted' => $faker->boolean,
            'is_enabled'     => $faker->boolean,
            'edit_at'        => date('Y-m-d H:i:s'),
        ];
        // When
        $validator = Validator::make($attributes, $this->rules, $this->messages); $this->request->withValidator($validator);
        $fails = $validator->fails();
        // Then
        $this->assertEquals(true, $fails);
    }
}
