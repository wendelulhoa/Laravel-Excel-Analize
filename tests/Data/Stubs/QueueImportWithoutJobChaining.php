<?php

namespace Analize\Excel\Tests\Data\Stubs;

use Analize\Excel\Concerns\Importable;
use Analize\Excel\Concerns\ShouldQueueWithoutChain;
use Analize\Excel\Concerns\ToModel;
use Analize\Excel\Concerns\WithChunkReading;
use Analize\Excel\Concerns\WithEvents;
use Analize\Excel\Events\AfterImport;
use Analize\Excel\Events\BeforeImport;
use Analize\Excel\Reader;
use Analize\Excel\Tests\Data\Stubs\Database\User;
use PHPUnit\Framework\Assert;

class QueueImportWithoutJobChaining implements ToModel, WithChunkReading, WithEvents, ShouldQueueWithoutChain
{
    use Importable;

    public $queue;
    public $before = false;
    public $after  = false;

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
                $this->before = true;
            },
            AfterImport::class  => function (AfterImport $event) {
                Assert::assertInstanceOf(Reader::class, $event->reader);
                $this->after = true;
            },
        ];
    }
}
