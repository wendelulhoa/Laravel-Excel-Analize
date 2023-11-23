<?php

namespace Analize\Excel\Tests\Concerns;

use Analize\Excel\Concerns\Importable;
use Analize\Excel\Concerns\WithReadFilter;
use Analize\Excel\Tests\TestCase;
use Analize\PhpSpreadsheet\Reader\IReadFilter;
use PHPUnit\Framework\Assert;

class WithReadFilterTest extends TestCase
{
    /**
     * @test
     */
    public function can_register_custom_read_filter()
    {
        $export = new class implements WithReadFilter
        {
            use Importable;

            public function readFilter(): IReadFilter
            {
                return new class implements IReadFilter
                {
                    public function readCell($column, $row, $worksheetName = '')
                    {
                        // Assert read filter is being called.
                        // If assertion is not called, test will fail due to
                        // test having no other assertions.
                        Assert::assertTrue(true);

                        return true;
                    }
                };
            }
        };

        $export->toArray('import-users.xlsx');
    }
}
