<?php

namespace Analize\Excel\Tests\Data\Stubs;

use Analize\Excel\Concerns\ToModel;
use Analize\Excel\Concerns\WithBatchInserts;
use Analize\Excel\Concerns\WithChunkReading;
use Analize\Excel\Events\AfterBatch;
use Analize\Excel\Events\AfterChunk;

class ImportWithEventsChunksAndBatches extends ImportWithEvents implements WithBatchInserts, ToModel, WithChunkReading
{
    /**
     * @var callable
     */
    public $afterBatch;

    /**
     * @var callable
     */
    public $afterChunk;

    /**
     * @return array
     */
    public function registerEvents(): array
    {
        return parent::registerEvents() + [
            AfterBatch::class => $this->afterBatch ?? function () {
            },
            AfterChunk::class => $this->afterChunk ?? function () {
            },
        ];
    }

    public function model(array $row)
    {
    }

    public function batchSize(): int
    {
        return 100;
    }

    public function chunkSize(): int
    {
        return 500;
    }
}
