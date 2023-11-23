<?php

namespace Analize\Excel\Tests\Concerns;

use Analize\Excel\Concerns\Importable;
use Analize\Excel\Concerns\RemembersChunkOffset;
use Analize\Excel\Concerns\ToArray;
use Analize\Excel\Concerns\WithChunkReading;
use Analize\Excel\Tests\TestCase;

class RemembersChunkOffsetTest extends TestCase
{
    /**
     * @test
     */
    public function can_set_and_get_chunk_offset()
    {
        $import = new class
        {
            use Importable;
            use RemembersChunkOffset;
        };

        $import->setChunkOffset(50);

        $this->assertEquals(50, $import->getChunkOffset());
    }

    /**
     * @test
     */
    public function can_access_chunk_offset_on_import_to_array_in_chunks()
    {
        $import = new class implements ToArray, WithChunkReading
        {
            use Importable;
            use RemembersChunkOffset;

            public $offsets = [];

            public function array(array $array)
            {
                $this->offsets[] = $this->getChunkOffset();
            }

            public function chunkSize(): int
            {
                return 2000;
            }
        };

        $import->import('import-batches.xlsx');

        $this->assertEquals([1, 2001, 4001], $import->offsets);
    }
}
