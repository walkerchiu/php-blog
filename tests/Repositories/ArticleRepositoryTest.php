<?php

namespace WalkerChiu\Blog;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use WalkerChiu\Blog\Models\Entities\Blog;
use WalkerChiu\Blog\Models\Entities\Article;
use WalkerChiu\Blog\Models\Entities\ArticleLang;
use WalkerChiu\Blog\Models\Repositories\ArticleRepository;

class ArticleRepositoryTest extends \Orchestra\Testbench\TestCase
{
    use RefreshDatabase;

    protected $repository;

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

        $this->repository = $this->app->make(ArticleRepository::class);
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
     * A basic functional test on BlogRepository.
     *
     * For WalkerChiu\Core\Models\Repositories\Repository
     *
     * @return void
     */
    public function testBlogRepository()
    {
        // Config
        Config::set('wk-core.onoff.core-lang_core', 0);
        Config::set('wk-blog.onoff.core-lang_core', 0);
        Config::set('wk-core.lang_log', 1);
        Config::set('wk-blog.lang_log', 1);
        Config::set('wk-core.soft_delete', 1);
        Config::set('wk-blog.soft_delete', 1);

        // Give
        $faker = \Faker\Factory::create();
        $db_blog = factory(Blog::class)->create();
        $id_list = [];
        for ($i=1; $i<=3; $i++) {
            $record = $this->repository->save([
                'blog_id'        => $db_blog->id,
                'identifier'     => $faker->slug,
                'can_comment'    => $faker->boolean,
                'can_search'     => $faker->boolean,
                'is_highlighted' => $faker->boolean,
                'is_enabled'     => $faker->boolean,
                'edit_at'        => date('Y-m-d H:i:s'),
                'title'          => $faker->name,
                'description'    => $faker->text,
                'content'        => $faker->text,
            ]);
            array_push($id_list, $record->id);
        }

        // Get and Count records after creation
            // When
            $records = $this->repository->get();
            $count   = $this->repository->count();
            // Then
            $this->assertCount(3, $records);
            $this->assertEquals(3, $count);

        // Find someone
            // When
            $record = $this->repository->first();
            // Then
            $this->assertNotNull($record);

            // When
            $record = $this->repository->find($faker->uuid());
            // Then
            $this->assertNull($record);

        // Delete someone
            // When
            $this->repository->deleteByIds([$id_list[0]]);
            $count = $this->repository->count();
            // Then
            $this->assertEquals(2, $count);

            // When
            $this->repository->deleteByExceptIds([$id_list[2]]);
            $count = $this->repository->count();
            $record = $this->repository->find($id_list[2]);
            // Then
            $this->assertEquals(1, $count);
            $this->assertNotNull($record);

            // When
            $count = $this->repository->where('id', '>', 0)->count();
            // Then
            $this->assertEquals(1, $count);

            // When
            $count = $this->repository->whereWithTrashed('id', '>', 0)->count();
            // Then
            $this->assertEquals(3, $count);

            // When
            $count = $this->repository->whereOnlyTrashed('id', '>', 0)->count();
            // Then
            $this->assertEquals(2, $count);

        // Force delete someone
            // When
            $this->repository->forcedeleteByIds([$id_list[2]]);
            $records = $this->repository->get();
            // Then
            $this->assertCount(0, $records);

        // Restore records
            // When
            $this->repository->restoreByIds([$id_list[0], $id_list[1]]);
            $count = $this->repository->count();
            // Then
            $this->assertEquals(2, $count);
    }

    /**
     * Unit test about Lang creation on BlogRepository.
     *
     * For WalkerChiu\Core\Models\Repositories\RepositoryHasmorphTrait
     *     WalkerChiu\Blog\Models\Repositories\BlogRepository
     *
     * @return void
     */
    public function testcreateLangWithoutCheck()
    {
        // Config
        Config::set('wk-core.onoff.core-lang_core', 0);
        Config::set('wk-blog.onoff.core-lang_core', 0);
        Config::set('wk-core.lang_log', 1);
        Config::set('wk-blog.lang_log', 1);
        Config::set('wk-core.soft_delete', 1);
        Config::set('wk-blog.soft_delete', 1);

        // Give
        $faker = \Faker\Factory::create();

        DB::table(config('wk-core.table.user'))->insert([
            'name'     => $faker->username,
            'email'    => $faker->email,
            'password' => $faker->password
        ]);
        $db_blog = factory(Blog::class)->create();
        $db_morph_1 = factory(Article::class)->create(['blog_id' => $db_blog->id]);

        // Find record
            // When
            $record = $this->repository->first($db_morph_1->id);
            // Then
            $this->assertNotNull($record);
    }

    /**
     * Unit test about Enable and Disable on BlogRepository.
     *
     * For WalkerChiu\Core\Models\Repositories\RepositoryHasmorphTrait
     *     WalkerChiu\Blog\Models\Repositories\BlogRepository
     *
     * @return void
     */
    public function testEnableAndDisable()
    {
        // Config
        Config::set('wk-core.onoff.core-lang_core', 0);
        Config::set('wk-blog.onoff.core-lang_core', 0);
        Config::set('wk-core.lang_log', 1);
        Config::set('wk-blog.lang_log', 1);
        Config::set('wk-core.soft_delete', 1);
        Config::set('wk-blog.soft_delete', 1);

        $faker = \Faker\Factory::create();

        // Give
        DB::table(config('wk-core.table.user'))->insert([
            'name'     => $faker->username,
            'email'    => $faker->email,
            'password' => $faker->password
        ]);
        $db_blog = factory(Blog::class)->create();
        $db_morph_1 = factory(Article::class)->create(['blog_id' => $db_blog->id, 'is_enabled' => 1]);
        $db_morph_2 = factory(Article::class)->create(['blog_id' => $db_blog->id]);
        $db_morph_3 = factory(Article::class)->create(['blog_id' => $db_blog->id]);
        $db_morph_4 = factory(Article::class)->create(['blog_id' => $db_blog->id]);

        // Count records
            // When
            $count = $this->repository->count();
            $count_enabled = $this->repository->ofEnabled()->count();
            $count_disabled = $this->repository->ofDisabled()->count();
            // Then
            $this->assertEquals(4, $count);
            $this->assertEquals(1, $count_enabled);
            $this->assertEquals(3, $count_disabled);

        // Enable records
            // When
            $this->repository->whereToEnable('id', '=', $db_morph_4->id);
            $count_enabled = $this->repository->ofEnabled()->count();
            $count_disabled = $this->repository->ofDisabled()->count();
            // Then
            $this->assertEquals(2, $count_enabled);
            $this->assertEquals(2, $count_disabled);

        // Disable records
            // When
            $this->repository->whereToDisable('id', '>', 0);
            $count_enabled = $this->repository->ofEnabled()->count();
            $count_disabled = $this->repository->ofDisabled()->count();
            // Then
            $this->assertEquals(0, $count_enabled);
            $this->assertEquals(4, $count_disabled);
    }

    /**
     * Unit test about Query List on BlogRepository.
     *
     * For WalkerChiu\Core\Models\Repositories\RepositoryHasmorphTrait
     *     WalkerChiu\Blog\Models\Repositories\BlogRepository
     *
     * @return void
     */
    public function testQueryList()
    {
        // Config
        Config::set('wk-core.onoff.core-lang_core', 0);
        Config::set('wk-blog.onoff.core-lang_core', 0);
        Config::set('wk-core.lang_log', 1);
        Config::set('wk-blog.lang_log', 1);
        Config::set('wk-core.soft_delete', 1);
        Config::set('wk-blog.soft_delete', 1);

        $faker = \Faker\Factory::create();

        // Give
        DB::table(config('wk-core.table.user'))->insert([
            'name'     => $faker->username,
            'email'    => $faker->email,
            'password' => $faker->password
        ]);
        $db_blog = factory(Blog::class)->create();
        $db_morph_1 = factory(Article::class)->create(['blog_id' => $db_blog->id]);
        $db_morph_2 = factory(Article::class)->create(['blog_id' => $db_blog->id]);
        $db_morph_3 = factory(Article::class)->create(['blog_id' => $db_blog->id]);
        $db_morph_4 = factory(Article::class)->create(['blog_id' => $db_blog->id]);

        // Get query
            // When
            sleep(1);
            $this->repository->find($db_morph_3->id)->touch();
            $records = $this->repository->ofNormal()->get();
            // Then
            $this->assertCount(4, $records);

            // When
            $record = $records->first();
            // Then
            $this->assertArrayNotHasKey('deleted_at', $record->toArray());
            $this->assertEquals($db_morph_3->id, $record->id);

        // Get query of trashed records
            // When
            $this->repository->deleteByIds([$db_morph_4->id]);
            $this->repository->deleteByIds([$db_morph_1->id]);
            $records = $this->repository->ofTrash()->get();
            // Then
            $this->assertCount(2, $records);

            // When
            $record = $records->first();
            // Then
            $this->assertArrayHasKey('deleted_at', $record);
            $this->assertEquals($db_morph_1->id, $record->id);
    }

    /**
     * Unit test about FormTrait on BlogRepository.
     *
     * For WalkerChiu\Core\Models\Repositories\RepositoryHasmorphTrait
     *     WalkerChiu\Blog\Models\Repositories\BlogRepository
     *     WalkerChiu\Core\Models\Forms\FormTrait
     *
     * @return void
     */
    public function testFormTrait()
    {
        // Config
        Config::set('wk-core.onoff.core-lang_core', 0);
        Config::set('wk-blog.onoff.core-lang_core', 0);
        Config::set('wk-core.lang_log', 1);
        Config::set('wk-blog.lang_log', 1);
        Config::set('wk-core.soft_delete', 1);
        Config::set('wk-blog.soft_delete', 1);

        $faker = \Faker\Factory::create();

        // Name
            // Give
            DB::table(config('wk-core.table.user'))->insert([
                'name'     => $faker->username,
                'email'    => $faker->email,
                'password' => $faker->password
            ]);
            $db_blog = factory(Blog::class)->create();
            $db_morph_1 = factory(Article::class)->create(['blog_id' => $db_blog->id]);
            $db_morph_2 = factory(Article::class)->create(['blog_id' => $db_blog->id]);

        // Identifier
            // Give
            $db_morph_3 = factory(Article::class)->create(['blog_id' => $db_blog->id, 'identifier' => 'A123']);
            $db_morph_4 = factory(Article::class)->create(['blog_id' => $db_blog->id, 'identifier' => 'A124']);
            $db_morph_5 = factory(Article::class)->create(['blog_id' => $db_blog->id, 'identifier' => 'A125', 'is_enabled' => 1]);
            // When
            $result_1 = $this->repository->checkExistIdentifier(null, 'A123');
            $result_2 = $this->repository->checkExistIdentifier($db_morph_3->id, 'A123');
            $result_3 = $this->repository->checkExistIdentifier($db_morph_3->id, 'A124');
            $result_4 = $this->repository->checkExistIdentifierOfEnabled($db_morph_4->id, 'A124');
            $result_5 = $this->repository->checkExistIdentifierOfEnabled($db_morph_4->id, 'A125');
            // Then
            $this->assertTrue($result_1);
            $this->assertTrue(!$result_2);
            $this->assertTrue($result_3);
            $this->assertTrue(!$result_4);
            $this->assertTrue($result_5);
    }
}
