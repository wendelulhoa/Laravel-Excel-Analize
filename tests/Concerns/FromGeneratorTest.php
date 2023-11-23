<?php

namespace Analize\Excel\Tests\Concerns;

use Generator;
use Analize\Excel\Concerns\Exportable;
use Analize\Excel\Concerns\FromGenerator;
use Analize\Excel\Tests\TestCase;

class FromGeneratorTest extends TestCase
{
    /**
     * @test
     */
    public function can_export_from_generator()
    {
        $export = new class implements FromGenerator
        {
            use Exportable;

            /**
             * @return Generator;
             */
            public function generator(): Generator
            {
                for ($i = 1; $i <= 2; $i++) {
                    yield ['test', 'test'];
                }
            }
        };

        $response = $export->store('from-generator-store.xlsx');

        $this->assertTrue($response);

        $contents = $this->readAsArray(__DIR__ . '/../Data/Disks/Local/from-generator-store.xlsx', 'Xlsx');

        $this->assertEquals(iterator_to_array($export->generator()), $contents);
    }
}
