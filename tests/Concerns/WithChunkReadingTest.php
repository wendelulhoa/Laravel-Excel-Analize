<?php

namespace Analize\Excel\Tests\Concerns;

use DateTime;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Analize\Excel\Concerns\Importable;
use Analize\Excel\Concerns\ToArray;
use Analize\Excel\Concerns\ToModel;
use Analize\Excel\Concerns\WithBatchInserts;
use Analize\Excel\Concerns\WithChunkReading;
use Analize\Excel\Concerns\WithEvents;
use Analize\Excel\Concerns\WithFormatData;
use Analize\Excel\Concerns\WithHeadingRow;
use Analize\Excel\Concerns\WithMultipleSheets;
use Analize\Excel\Events\AfterImport;
use Analize\Excel\Events\BeforeImport;
use Analize\Excel\Events\ImportFailed;
use Analize\Excel\Reader;
use Analize\Excel\Tests\Data\Stubs\Database\Group;
use Analize\Excel\Tests\Data\Stubs\Database\User;
use Analize\Excel\Tests\TestCase;
use Analize\PhpSpreadsheet\Shared\Date;
use PHPUnit\Framework\Assert;
use Throwable;

class WithChunkReadingTest extends TestCase
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
    public function can_import_to_model_in_chunks_un()
    {
        DB::connection()->enableQueryLog();

        $import = new class implements ToModel, WithChunkReading, WithEvents
        {
            use Importable;

            public $before = 0;
            public $after  = 0;

            /**
             * @param  array  $row
             * @return Model|null
             */
            public function model(array $row)
            {
                return new User([
                    'name'     => $row[0],
                    'email'    => $row[1],
                    'password' => 'secret',
                ]);
            }

            /**
             * @return int
             */
            public function chunkSize(): int
            {
                return 1;
            }

            /**
             * @return array
             */
            public function registerEvents(): array
            {
                return [
                    BeforeImport::class => function (BeforeImport $event) {
                        Assert::assertInstanceOf(Reader::class, $event->reader);
                        $this->before++;
                    },
                    AfterImport::class  => function (AfterImport $event) {
                        Assert::assertInstanceOf(Reader::class, $event->reader);
                        $this->after++;
                    },
                ];
            }
        };

        $import->import('import-users.xlsx');

        $this->assertCount(2, DB::getQueryLog());
        DB::connection()->disableQueryLog();

        $this->assertEquals(1, $import->before, 'BeforeImport was not called or more than once.');
        $this->assertEquals(1, $import->after, 'AfterImport was not called or more than once.');
    }

    /**
     * @test
     */
    public function can_import_to_model_in_chunks_and_insert_in_batches()
    {
        DB::connection()->enableQueryLog();

        $import = new class implements ToModel, WithChunkReading, WithBatchInserts
        {
            use Importable;

            /**
             * @param  array  $row
             * @return Model|null
             */
            public function model(array $row)
            {
                return new Group([
                    'name' => $row[0],
                ]);
            }

            /**
             * @return int
             */
            public function chunkSize(): int
            {
                return 1000;
            }

            /**
             * @return int
             */
            public function batchSize(): int
            {
                return 1000;
            }
        };

        $import->import('import-batches.xlsx');

        $this->assertCount(5000 / $import->batchSize(), DB::getQueryLog());
        DB::connection()->disableQueryLog();
    }

    /**
     * @test
     */
    public function can_import_to_model_in_chunks_and_insert_in_batches_with_heading_row()
    {
        DB::connection()->enableQueryLog();

        $import = new class implements ToModel, WithChunkReading, WithBatchInserts, WithHeadingRow
        {
            use Importable;

            /**
             * @param  array  $row
             * @return Model|null
             */
            public function model(array $row)
            {
                return new Group([
                    'name' => $row['name'],
                ]);
            }

            /**
             * @return int
             */
            public function chunkSize(): int
            {
                return 1000;
            }

            /**
             * @return int
             */
            public function batchSize(): int
            {
                return 1000;
            }
        };

        $import->import('import-batches-with-heading-row.xlsx');

        $this->assertCount(5000 / $import->batchSize(), DB::getQueryLog());
        DB::connection()->disableQueryLog();
    }

    /**
     * @test
     */
    public function can_import_csv_in_chunks_and_insert_in_batches()
    {
        DB::connection()->enableQueryLog();

        $import = new class implements ToModel, WithChunkReading, WithBatchInserts
        {
            use Importable;

            /**
             * @param  array  $row
             * @return Model|null
             */
            public function model(array $row)
            {
                return new Group([
                    'name' => $row[0],
                ]);
            }

            /**
             * @return int
             */
            public function chunkSize(): int
            {
                return 1000;
            }

            /**
             * @return int
             */
            public function batchSize(): int
            {
                return 1000;
            }
        };

        $import->import('import-batches.csv');

        $this->assertCount(10, DB::getQueryLog());
        DB::connection()->disableQueryLog();
    }

    /**
     * @test
     */
    public function can_import_to_model_in_chunks_and_insert_in_batches_with_multiple_sheets()
    {
        DB::connection()->enableQueryLog();

        $import = new class implements ToModel, WithChunkReading, WithBatchInserts
        {
            use Importable;

            /**
             * @param  array  $row
             * @return Model|null
             */
            public function model(array $row)
            {
                return new Group([
                    'name' => $row[0],
                ]);
            }

            /**
             * @return int
             */
            public function chunkSize(): int
            {
                return 1000;
            }

            /**
             * @return int
             */
            public function batchSize(): int
            {
                return 1000;
            }
        };

        $import->import('import-batches-multiple-sheets.xlsx');

        $this->assertCount(10, DB::getQueryLog());
        DB::connection()->disableQueryLog();
    }

    /**
     * @test
     */
    public function can_import_to_array_in_chunks()
    {
        $import = new class implements ToArray, WithChunkReading, WithFormatData
        {
            use Importable;

            public $called = 0;

            /**
             * @param  array  $array
             */
            public function array(array $array)
            {
                $this->called++;

                Assert::assertCount(100, $array);
            }

            /**
             * @return int
             */
            public function chunkSize(): int
            {
                return 100;
            }
        };

        $import->import('import-batches.xlsx');

        $this->assertEquals(50, $import->called);
    }

    /**
     * @test
     */
    public function can_import_to_model_in_chunks_and_insert_in_batches_with_multiple_sheets_objects_by_index()
    {
        DB::connection()->enableQueryLog();

        $import = new class implements WithMultipleSheets, WithChunkReading
        {
            use Importable;

            /**
             * @return int
             */
            public function chunkSize(): int
            {
                return 1000;
            }

            /**
             * @return array
             */
            public function sheets(): array
            {
                return [
                    new class implements ToModel, WithBatchInserts
                    {
                        /**
                         * @param  array  $row
                         * @return Model|null
                         */
                        public function model(array $row)
                        {
                            return new Group([
                                'name' => $row[0],
                            ]);
                        }

                        /**
                         * @return int
                         */
                        public function batchSize(): int
                        {
                            return 1000;
                        }
                    },

                    new class implements ToModel, WithBatchInserts
                    {
                        /**
                         * @param  array  $row
                         * @return Model|null
                         */
                        public function model(array $row)
                        {
                            return new Group([
                                'name' => $row[0],
                            ]);
                        }

                        /**
                         * @return int
                         */
                        public function batchSize(): int
                        {
                            return 2000;
                        }
                    },
                ];
            }
        };

        $import->import('import-batches-multiple-sheets.xlsx');

        $this->assertCount(10, DB::getQueryLog());
        DB::connection()->disableQueryLog();
    }

    /**
     * @test
     */
    public function can_import_to_model_in_chunks_and_insert_in_batches_with_multiple_sheets_objects_by_name()
    {
        DB::connection()->enableQueryLog();

        $import = new class implements WithMultipleSheets, WithChunkReading
        {
            use Importable;

            /**
             * @return int
             */
            public function chunkSize(): int
            {
                return 1000;
            }

            /**
             * @return array
             */
            public function sheets(): array
            {
                return [
                    'Worksheet' => new class implements ToModel, WithBatchInserts
                    {
                        /**
                         * @param  array  $row
                         * @return Model|null
                         */
                        public function model(array $row)
                        {
                            return new Group([
                                'name' => $row[0],
                            ]);
                        }

                        /**
                         * @return int
                         */
                        public function batchSize(): int
                        {
                            return 1000;
                        }
                    },

                    'Worksheet2' => new class implements ToModel, WithBatchInserts
                    {
                        /**
                         * @param  array  $row
                         * @return Model|null
                         */
                        public function model(array $row)
                        {
                            return new Group([
                                'name' => $row[0],
                            ]);
                        }

                        /**
                         * @return int
                         */
                        public function batchSize(): int
                        {
                            return 2000;
                        }
                    },
                ];
            }
        };

        $import->import('import-batches-multiple-sheets.xlsx');

        $this->assertCount(10, DB::getQueryLog());
        DB::connection()->disableQueryLog();
    }

    /**
     * @test
     */
    public function can_catch_job_failed_in_chunks()
    {
        $import = new class implements ToModel, WithChunkReading, WithEvents
        {
            use Importable;

            public $failed = false;

            /**
             * @param  array  $row
             * @return Model|null
             */
            public function model(array $row)
            {
                throw new Exception('Something went wrong in the chunk');
            }

            /**
             * @return int
             */
            public function chunkSize(): int
            {
                return 1;
            }

            /**
             * @return array
             */
            public function registerEvents(): array
            {
                return [
                    ImportFailed::class => function (ImportFailed $event) {
                        Assert::assertInstanceOf(Throwable::class, $event->getException());
                        Assert::assertEquals('Something went wrong in the chunk', $event->e->getMessage());

                        $this->failed = true;
                    },
                ];
            }
        };

        try {
            $import->import('import-users.xlsx');
        } catch (Throwable $e) {
            $this->assertInstanceOf(Exception::class, $e);
            $this->assertEquals('Something went wrong in the chunk', $e->getMessage());
        }

        $this->assertTrue($import->failed, 'ImportFailed event was not called.');
    }

    /**
     * @test
     */
    public function can_import_to_array_and_format_in_chunks()
    {
        config()->set('excel.imports.read_only', false);

        $import = new class implements ToArray, WithChunkReading, WithFormatData
        {
            use Importable;

            /**
             * @param  array  $array
             */
            public function array(array $array)
            {
                Assert::assertCount(2, $array);
                Assert::assertCount(1, $array[0]);
                Assert::assertCount(1, $array[1]);
                Assert::assertIsString($array[0][0]);
                Assert::assertIsString($array[1][0]);
                Assert::assertEquals('01/12/22', $array[0][0]);
                Assert::assertEquals('2023-02-20', $array[1][0]);
            }

            /**
             * @return int
             */
            public function chunkSize(): int
            {
                return 2;
            }
        };

        $import->import('import-batches-with-date.xlsx');
    }

    /**
     * @test
     */
    public function can_import_to_array_in_chunks_without_formatting()
    {
        config()->set('excel.imports.read_only', true);

        $import = new class implements ToArray, WithChunkReading
        {
            use Importable;

            /**
             * @param  array  $array
             */
            public function array(array $array)
            {
                Assert::assertCount(2, $array);
                Assert::assertCount(1, $array[0]);
                Assert::assertCount(1, $array[1]);
                Assert::assertIsInt($array[0][0]);
                Assert::assertIsInt($array[1][0]);
                Assert::assertEquals((int) Date::dateTimeToExcel(DateTime::createFromFormat('Y-m-d', '2022-12-01')->setTime(0, 0, 0, 0)), $array[0][0]);
                Assert::assertEquals((int) Date::dateTimeToExcel(DateTime::createFromFormat('Y-m-d', '2023-02-20')->setTime(0, 0, 0, 0)), $array[1][0]);
            }

            /**
             * @return int
             */
            public function chunkSize(): int
            {
                return 2;
            }
        };

        $import->import('import-batches-with-date.xlsx');
    }
}
