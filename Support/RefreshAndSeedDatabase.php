<?php

namespace Tests\Support;

use Illuminate\Foundation\Testing\RefreshDatabase;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Testing\RefreshDatabaseState;

trait RefreshAndSeedDatabase
{
    use RefreshDatabase {
        refreshInMemoryDatabase as baseRefreshInMemoryDatabase;
    }

    /**
     * Refresh the in-memory database.
     *
     * @return void
     */
    protected function refreshInMemoryDatabase()
    {
        $this->baseRefreshInMemoryDatabase();

        $this->artisan('db:seed');
        $this->app[Kernel::class]->setArtisan(null);
    }

    /**
     * Refresh a conventional test database.
     *
     * @return void
     */
    protected function refreshTestDatabase()
    {
        /*
        Comment the call to $this->migrateAndSeedDatabase() to avoid running migrate:fresh and db:seed.
        This makes tests much faster if you are running tests repeatedly and know that the test db has
        already been migrated and seeded. You can safely do this because each test is wrapped in a db
        transaction that is rolled back after each test via $this->beginDatabaseTransaction().
         */

        /*** DO NOT COMMIT THIS FILE WITH THE FOLLOWING LINE COMMENTED ***/
        $this->migrateAndSeedDatabase();
        $this->beginDatabaseTransaction();
    }

    protected function migrateAndSeedDatabase()
    {
        if (!RefreshDatabaseState::$migrated) {
            $args = [
                '--seed' => true,
            ];
            if ($this->shouldDropViews()) {
                $args['--drop-views'] = true;
            }
            $this->artisan('migrate:fresh', $args);
            $this->app[Kernel::class]->setArtisan(null);

            RefreshDatabaseState::$migrated = true;
        }
    }
}
