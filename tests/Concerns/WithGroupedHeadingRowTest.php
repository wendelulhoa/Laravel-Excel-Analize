<?php

namespace Analize\Excel\Tests\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Analize\Excel\Concerns\Importable;
use Analize\Excel\Concerns\OnEachRow;
use Analize\Excel\Concerns\ToArray;
use Analize\Excel\Concerns\ToCollection;
use Analize\Excel\Concerns\ToModel;
use Analize\Excel\Concerns\WithGroupedHeadingRow;
use Analize\Excel\Row;
use Analize\Excel\Tests\Data\Stubs\Database\User;
use Analize\Excel\Tests\TestCase;
use PHPUnit\Framework\Assert;

class WithGroupedHeadingRowTest extends TestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadLaravelMigrations(['--database' => 'testing']);
        $this->loadMigrationsFrom(dirname(__DIR__) . '/Data/Stubs/Database/Migrations');
    }

    /**
     * @test
     */
    public function can_import_to_array_with_grouped_headers()
    {
        $import = new class implements ToArray, WithGroupedHeadingRow
        {
            use Importable;

            /**
             * @param  array  $array
             */
            public function array(array $array)
            {
                Assert::assertEquals([
                    [
                        'name'    => 'Patrick Brouwers',
                        'email'   => 'patrick@maatwebsite.nl',
                        'options' => [
                            'laravel',
                            'excel',
                        ],
                    ],
                ], $array);
            }
        };

        $import->import('import-users-with-grouped-headers.xlsx');
    }

    /**
     * @test
     */
    public function can_import_oneachrow_with_grouped_headers()
    {
        $import = new class implements OnEachRow, WithGroupedHeadingRow
        {
            use Importable;

            /**
             * @param  \Analize\Excel\Row  $row
             * @return void
             */
            public function onRow(Row $row)
            {
                Assert::assertEquals(
                    [
                        'name'    => 'Patrick Brouwers',
                        'email'   => 'patrick@maatwebsite.nl',
                        'options' => [
                            'laravel',
                            'excel',
                        ],
                    ], $row->toArray());
            }
        };

        $import->import('import-users-with-grouped-headers.xlsx');
    }

    /**
     * @test
     */
    public function can_import_to_collection_with_grouped_headers()
    {
        $import = new class implements ToCollection, WithGroupedHeadingRow
        {
            use Importable;

            public $called = false;

            /**
             * @param  Collection  $collection
             */
            public function collection(Collection $collection)
            {
                $this->called = true;

                Assert::assertEquals([
                    [
                        'name'    => 'Patrick Brouwers',
                        'email'   => 'patrick@maatwebsite.nl',
                        'options' => [
                            'laravel',
                            'excel',
                        ],
                    ],
                ], $collection->toArray());
            }
        };

        $import->import('import-users-with-grouped-headers.xlsx');

        $this->assertTrue($import->called);
    }

    /**
     * @test
     */
    public function can_import_each_row_to_model_with_grouped_headers()
    {
        $import = new class implements ToModel, WithGroupedHeadingRow
        {
            use Importable;

            /**
             * @param  array  $row
             * @return Model
             */
            public function model(array $row): Model
            {
                return new User([
                    'name'     => $row['name'],
                    'email'    => $row['email'],
                    'password' => 'secret',
                    'options'  => $row['options'],
                ]);
            }
        };

        $import->import('import-users-with-grouped-headers.xlsx');

        $this->assertDatabaseHas('users', [
            'name'    => 'Patrick Brouwers',
            'email'   => 'patrick@maatwebsite.nl',
            'options' => '["laravel","excel"]',
        ]);
    }
}
