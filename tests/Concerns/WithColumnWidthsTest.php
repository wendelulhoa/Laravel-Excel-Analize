<?php

namespace Analize\Excel\Tests\Concerns;

use Analize\Excel\Concerns\Exportable;
use Analize\Excel\Concerns\FromArray;
use Analize\Excel\Concerns\WithColumnWidths;
use Analize\Excel\Tests\TestCase;

class WithColumnWidthsTest extends TestCase
{
    /**
     * @test
     */
    public function can_set_column_width()
    {
        $export = new class implements FromArray, WithColumnWidths
        {
            use Exportable;

            public function columnWidths(): array
            {
                return [
                    'A' => 55,
                ];
            }

            public function array(): array
            {
                return [
                    ['AA'],
                    ['BB'],
                ];
            }
        };

        $export->store('with-column-widths.xlsx');

        $spreadsheet = $this->read(__DIR__ . '/../Data/Disks/Local/with-column-widths.xlsx', 'Xlsx');

        $this->assertEquals(55, $spreadsheet->getActiveSheet()->getColumnDimension('A')->getWidth());
    }
}
