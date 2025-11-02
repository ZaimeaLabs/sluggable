<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        if (! Schema::hasTable('posts')) {
            Schema::create('posts', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name')->nullable();
                $table->string('other_field')->nullable();
                $table->string('url')->nullable();
            });
        }

        if (! Schema::hasTable('posts_soft_deleted')) {
            Schema::create('posts_soft_deletes', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name')->nullable();
                $table->string('other_field')->nullable();
                $table->string('url')->nullable();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('scopeable_models')) {
            Schema::create('scopeable_models', function (Blueprint $table) {
                $table->increments('id');
                $table->text('name')->nullable();
                $table->text('slug')->nullable();
                $table->unsignedInteger('scope_id')->nullable();
            });
        }
    }

    protected function tearDown(): void
    {
        // Drop tables to ensure clean state between tests (SQLite in-memory gets cleared,
        // but for safety, drop if exists)
        Schema::dropIfExists('posts');
        Schema::dropIfExists('posts_soft_deletes');
        Schema::dropIfExists('scopeable_models');

        parent::tearDown();
    }
}
