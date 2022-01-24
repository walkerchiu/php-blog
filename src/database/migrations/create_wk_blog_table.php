<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateWkBlogTable extends Migration
{
    public function up()
    {
        Schema::create(config('wk-core.table.blog.blogs'), function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('user_id')->nullable();
            $table->string('identifier');
            $table->string('language')->nullable();
            $table->text('script_head')->nullable();
            $table->text('script_footer')->nullable();
            $table->json('options')->nullable();
            $table->boolean('is_highlighted')->default(0);
            $table->boolean('is_enabled')->default(0);

            $table->timestampsTz();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')
                  ->on(config('wk-core.table.user'))
                  ->onDelete('set null')
                  ->onUpdate('cascade');

            $table->primary('id');
            $table->index('identifier');
            $table->index('is_highlighted');
            $table->index('is_enabled');
        });
        if (!config('wk-blog.onoff.core-lang_core')) {
            Schema::create(config('wk-core.table.blog.blogs_lang'), function (Blueprint $table) {
                $table->uuid('id');
                $table->uuidMorphs('morph');
                $table->uuid('user_id')->nullable();
                $table->string('code');
                $table->string('key');
                $table->longText('value')->nullable();
                $table->boolean('is_current')->default(1);

                $table->timestampsTz();
                $table->softDeletes();

                $table->foreign('user_id')->references('id')
                    ->on(config('wk-core.table.user'))
                    ->onDelete('set null')
                    ->onUpdate('cascade');

                $table->primary('id');
            });
        }
        Schema::create(config('wk-core.table.blog.articles'), function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('blog_id');
            $table->uuid('newest_id')->nullable();
            $table->string('identifier');
            $table->string('title');
            $table->string('description');
            $table->longText('content');
            $table->string('cover')->nullable();
            $table->string('keywords')->nullable();
            $table->boolean('can_comment')->default(1);
            $table->boolean('can_search')->default(1);
            $table->boolean('is_highlighted')->default(0);
            $table->boolean('is_enabled')->default(0);
            $table->timestamp('edit_at')->nullable();

            $table->timestampsTz();
            $table->softDeletes();

            $table->foreign('blog_id')->references('id')
                  ->on(config('wk-core.table.blog.blogs'))
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->primary('id');
            $table->index('newest_id');
            $table->index('identifier');
            $table->index('is_highlighted');
            $table->index('is_enabled');
        });
        Schema::create(config('wk-core.table.blog.tags'), function (Blueprint $table) {
            $table->uuid('id');
            $table->string('identifier');

            $table->primary('id');
            $table->index('identifier');
        });
        Schema::create(config('wk-core.table.blog.tags_morphs'), function (Blueprint $table) {
            $table->uuid('tag_id')->nullable();
            $table->uuidMorphs('morph');

            $table->foreign('tag_id')->references('id')
                  ->on(config('wk-core.table.blog.tags'))
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->index(['tag_id', 'morph_type', 'morph_id']);
        });
        Schema::create(config('wk-core.table.blog.likes'), function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('user_id')->nullable();
            $table->uuidMorphs('morph');

            $table->timestampsTz();

            $table->foreign('user_id')->references('id')
                  ->on(config('wk-core.table.user'))
                  ->onDelete('set null')
                  ->onUpdate('cascade');

            $table->primary('id');
        });
        Schema::create(config('wk-core.table.blog.touches'), function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('user_id')->nullable();
            $table->uuid('article_id');
            $table->json('header');

            $table->timestampsTz();

            $table->foreign('user_id')->references('id')
                  ->on(config('wk-core.table.user'))
                  ->onDelete('set null')
                  ->onUpdate('cascade');

            $table->foreign('article_id')->references('id')
                  ->on(config('wk-core.table.blog.articles'))
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->primary('id');
        });
        DB::statement('ALTER TABLE `'.config('wk-core.table.blog.touches').'` ADD `ip` VARBINARY(16) AFTER `header`;');
        Schema::create(config('wk-core.table.blog.follows'), function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('user_id');
            $table->uuidMorphs('morph');

            $table->timestampsTz();

            $table->foreign('user_id')->references('id')
                  ->on(config('wk-core.table.user'))
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->primary('id');
        });
    }

    public function down() {
        Schema::dropIfExists(config('wk-core.table.blog.follows'));
        DB::statement('ALTER TABLE `'.config('wk-core.table.blog.touches').'` DROP COLUMN `ip`');
        Schema::dropIfExists(config('wk-core.table.blog.touches'));
        Schema::dropIfExists(config('wk-core.table.blog.likes'));
        Schema::dropIfExists(config('wk-core.table.blog.tags_morphs'));
        Schema::dropIfExists(config('wk-core.table.blog.tags'));
        Schema::dropIfExists(config('wk-core.table.blog.articles'));
        Schema::dropIfExists(config('wk-core.table.blog.blogs_lang'));
        Schema::dropIfExists(config('wk-core.table.blog.blogs'));
    }
}
