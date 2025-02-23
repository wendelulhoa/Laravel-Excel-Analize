<?php

namespace Analize\Excel\Tests\Concerns;

use Analize\Excel\Concerns\Importable;
use Analize\Excel\Concerns\SkipsEmptyRows;
use Analize\Excel\Concerns\ToArray;
use Analize\Excel\Concerns\WithColumnLimit;
use Analize\Excel\Tests\TestCase;
use PHPUnit\Framework\Assert;

class WithColumnLimitTest extends TestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadLaravelMigrations(['--database' => 'testing']);
    }

    /**
     * @test
     */
    public function can_import_to_array_with_column_limit()
    {
        $import = new class implements ToArray, WithColumnLimit
        {
            use Importable;

            /**
             * @param  array  $array
             */
            public function array(array $array)
            {
                Assert::assertEquals([
                    [
                        'Patrick Brouwers',
                    ],
                    [
                        'Taylor Otwell',
                    ],
                ], $array);
            }

            public function endColumn(): string
            {
                return 'A';
            }
        };

        $import->import('import-users.xlsx');
    }

    /**
     * @test
     */
    public function can_import_to_array_with_column_limit_and_skips_empty_rows()
    {
        $import = new class implements ToArray, WithColumnLimit, SkipsEmptyRows
        {
            use Importable;

            /**
             * @param  array  $array
             */
            public function array(array $array)
            {
                Assert::assertEquals([
                    [
                        'Test1',
                        'Test2',
                        null,
                        null,
                    ],
                    [
                        'Test3',
                        'Test4',
                        null,
                        null,
                    ],
                    [
                        'Test5',
                        'Test6',
                        null,
                        null,
                    ],
                ], $array);
            }

            public function endColumn(): string
            {
                return 'D';
            }
        };

        $import->import('import-empty-rows.xlsx');
    }
}
