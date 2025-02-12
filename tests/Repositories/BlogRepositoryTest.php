<?php

namespace WalkerChiu\Blog;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use WalkerChiu\Blog\Models\Entities\Blog;
use WalkerChiu\Blog\Models\Entities\BlogLang;
use WalkerChiu\Blog\Models\Repositories\BlogRepository;

class BlogRepositoryTest extends \Orchestra\Testbench\TestCase
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

        $this->repository = $this->app->make(BlogRepository::class);
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

        $id_list = [];
        for ($i=1; $i<=3; $i++) {
            $record = $this->repository->save([
                'identifier'     => $faker->slug,
                'is_highlighted' => $faker->boolean,
                'is_enabled'     => $faker->boolean
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
        factory(Blog::class)->create();

        // Find record
            // When
            $record = $this->repository->first();
            // Then
            $this->assertNotNull($record);

        // Create Lang
            // When
            $lang = $this->repository->createLangWithoutCheck(['morph_type' => get_class($record), 'morph_id' => $record->id, 'code' => 'en_us', 'key' => 'name', 'value' => 'Hello']);
            // Then
            $this->assertInstanceOf(BlogLang::class, $lang);
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
        $db_morph_1 = factory(Blog::class)->create(['is_enabled' => 1]);
        $db_morph_2 = factory(Blog::class)->create();
        $db_morph_3 = factory(Blog::class)->create();
        $db_morph_4 = factory(Blog::class)->create();

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
        $db_morph_1 = factory(Blog::class)->create();
        $db_morph_2 = factory(Blog::class)->create();
        $db_morph_3 = factory(Blog::class)->create();
        $db_morph_4 = factory(Blog::class)->create();

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
            $db_morph_1 = factory(Blog::class)->create();
            $db_morph_2 = factory(Blog::class)->create();
            $this->repository->createLangWithoutCheck(['morph_id' => $db_morph_1->id, 'morph_type' => Blog::class, 'code' => 'en_us', 'key' => 'name', 'value' => 'Hello']);
            $this->repository->createLangWithoutCheck(['morph_id' => $db_morph_2->id, 'morph_type' => Blog::class, 'code' => 'zh_tw', 'key' => 'name', 'value' => '您好']);
            // When
            $result_1 = $this->repository->checkExistName('en_us', null, 'Hello');
            $result_2 = $this->repository->checkExistName('en_us', null, 'Hi');
            $result_3 = $this->repository->checkExistName('en_us', $db_morph_1->id, 'Hello');
            $result_4 = $this->repository->checkExistName('en_us', $db_morph_1->id, '您好');
            $result_5 = $this->repository->checkExistName('zh_tw', $db_morph_1->id, '您好');
            $result_6 = $this->repository->checkExistNameOfEnabled('en_us', null, 'Hello');
            // Then
            $this->assertTrue($result_1);
            $this->assertTrue(!$result_2);
            $this->assertTrue(!$result_3);
            $this->assertTrue(!$result_4);
            $this->assertTrue($result_5);
            $this->assertTrue(!$result_6);

        // Identifier
            // Give
            $db_morph_3 = factory(Blog::class)->create(['identifier' => 'A123']);
            $db_morph_4 = factory(Blog::class)->create(['identifier' => 'A124']);
            $db_morph_5 = factory(Blog::class)->create(['identifier' => 'A125', 'is_enabled' => 1]);
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

    /**
     * Unit test about Auto Complete on BlogRepository.
     *
     * For WalkerChiu\Core\Models\Repositories\RepositoryHasmorphTrait
     *     WalkerChiu\Blog\Models\Repositories\BlogRepository
     *
     * @return void
     */
    public function testAutoComplete()
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
        $db_morph_1 = factory(Blog::class)->create(['is_enabled' => 1]);
        $db_morph_2 = factory(Blog::class)->create(['is_enabled' => 1]);
        $this->repository->createLangWithoutCheck(['morph_id' => $db_morph_1->id, 'morph_type' => Blog::class, 'code' => 'en_us', 'key' => 'name', 'value' => 'Hello']);
        $this->repository->createLangWithoutCheck(['morph_id' => $db_morph_1->id, 'morph_type' => Blog::class, 'code' => 'en_us', 'key' => 'description', 'value' => 'Good Morning!']);
        $this->repository->createLangWithoutCheck(['morph_id' => $db_morph_1->id, 'morph_type' => Blog::class, 'code' => 'en_us', 'key' => 'name', 'value' => 'Hello World']);
        $this->repository->createLangWithoutCheck(['morph_id' => $db_morph_1->id, 'morph_type' => Blog::class, 'code' => 'zh_tw', 'key' => 'name', 'value' => '您好']);
        $this->repository->createLangWithoutCheck(['morph_id' => $db_morph_1->id, 'morph_type' => Blog::class, 'code' => 'zh_tw', 'key' => 'name', 'value' => '早安']);
        $this->repository->createLangWithoutCheck(['morph_id' => $db_morph_2->id, 'morph_type' => Blog::class, 'code' => 'en_us', 'key' => 'name', 'value' => 'Bye']);

        // List array by name of enabled records
            // When
            $records = $this->repository->autoCompleteNameOfEnabled('en_us', 'H');
            // Then
            $this->assertCount(1, $records);

            // When
            $records = $this->repository->autoCompleteNameOfEnabled('zh_tw', 'H');
            // Then
            $this->assertCount(0, $records);
    }

    /**
     * Unit test about List on BlogRepository.
     *
     * For WalkerChiu\Core\Models\Repositories\RepositoryHasmorphTrait
     *     WalkerChiu\Blog\Models\Repositories\BlogRepository
     *
     * @return void
     */
    public function testList()
    {
        // Config
        Config::set('wk-core.onoff.core-lang_core', 0);
        Config::set('wk-blog.onoff.core-lang_core', 0);
        Config::set('wk-core.lang_log', 1);
        Config::set('wk-blog.lang_log', 1);
        Config::set('wk-core.soft_delete', 1);
        Config::set('wk-blog.soft_delete', 1);
        Config::set('wk-blog.output_format', 'array');

        $faker = \Faker\Factory::create();

        // When
        $records = $this->repository->list('en_us', [], null, true);
        // Then
        $this->assertTrue(is_array($records));
        $this->assertTrue(empty($records));

        // Give
        $db_morph_1  = factory(Blog::class)->create();
        $db_morph_2  = factory(Blog::class)->create();
        $db_morph_3  = factory(Blog::class)->create(['is_enabled' => 1]);
        $db_morph_4  = factory(Blog::class)->create(['is_enabled' => 1]);
        $db_morph_5  = factory(Blog::class)->create(['is_enabled' => 1]);
        $db_morph_6  = factory(Blog::class)->create(['is_enabled' => 1]);
        $db_morph_7  = factory(Blog::class)->create(['is_enabled' => 1]);
        $db_morph_8  = factory(Blog::class)->create(['is_enabled' => 1]);
        $db_morph_9  = factory(Blog::class)->create(['is_enabled' => 1]);
        $db_morph_10 = factory(Blog::class)->create();

        // Give
        $this->repository->createLangWithoutCheck(['morph_id' => $db_morph_3->id, 'morph_type' => Blog::class, 'code' => 'en_us', 'key' => 'name', 'value' => 'Hello']);
        $this->repository->createLangWithoutCheck(['morph_id' => $db_morph_3->id, 'morph_type' => Blog::class, 'code' => 'en_us', 'key' => 'description', 'value' => 'Good Morning!']);
        $this->repository->createLangWithoutCheck(['morph_id' => $db_morph_3->id, 'morph_type' => Blog::class, 'code' => 'en_us', 'key' => 'name', 'value' => 'Hello World']);
        $this->repository->createLangWithoutCheck(['morph_id' => $db_morph_3->id, 'morph_type' => Blog::class, 'code' => 'zh_tw', 'key' => 'name', 'value' => '您好']);
        $this->repository->createLangWithoutCheck(['morph_id' => $db_morph_4->id, 'morph_type' => Blog::class, 'code' => 'en_us', 'key' => 'name', 'value' => 'Hi']);

        // When
        $records = $this->repository->list('en_us', [], null, true);
        // Then
        $this->assertCount(2, $records);

        // When
        $records_1 = $this->repository->list('en_us', ['name' => 'H']);
        $records_2 = $this->repository->list('en_us', ['name' => 'H', 'description' => 'G']);
        $records_3 = $this->repository->list('en_us', ['name' => 'Hi']);
        // Then
        $this->assertEquals(2, $records_1->count());
        $this->assertEquals(1, $records_2->count());
        $this->assertEquals(1, $records_3->count());
    }
}
