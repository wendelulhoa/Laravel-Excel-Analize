<?php

namespace Analize\Excel\Tests\Concerns;

use Analize\Excel\Concerns\Importable;
use Analize\Excel\Concerns\OnEachRow;
use Analize\Excel\Row;
use Analize\Excel\Tests\TestCase;
use PHPUnit\Framework\Assert;

class OnEachRowTest extends TestCase
{
    /**
     * @test
     */
    public function can_import_each_row_individually()
    {
        $import = new class implements OnEachRow
        {
            use Importable;

            public $called = 0;

            /**
             * @param  Row  $row
             */
            public function onRow(Row $row)
            {
                foreach ($row->getCellIterator() as $cell) {
                    Assert::assertEquals('test', $cell->getValue());
                }

                Assert::assertEquals([
                    'test', 'test',
                ], $row->toArray());

                Assert::assertEquals('test', $row[0]);

                $this->called++;
            }
        };

        $import->import('import.xlsx');

        $this->assertEquals(2, $import->called);
    }

    /**
     * @test
     */
    public function it_respects_the_end_column()
    {
        $import = new class implements OnEachRow
        {
            use Importable;

            /**
             * @param  Row  $row
             */
            public function onRow(Row $row)
            {
                // Accessing a row as an array calls toArray() without an end
                // column. This saves the row in the cache, so we have to
                // invalidate the cache once the end column changes
                $row[0];

                Assert::assertEquals([
                    'test',
                ], $row->toArray(null, false, true, 'A'));
            }
        };

        $import->import('import.xlsx');
    }
}
