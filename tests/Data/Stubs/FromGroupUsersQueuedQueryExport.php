<?php

namespace Analize\Excel\Tests\Data\Stubs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder;
use Analize\Excel\Concerns\Exportable;
use Analize\Excel\Concerns\FromQuery;
use Analize\Excel\Concerns\WithCustomChunkSize;
use Analize\Excel\Concerns\WithMapping;
use Analize\Excel\Tests\Data\Stubs\Database\Group;

class FromGroupUsersQueuedQueryExport implements FromQuery, WithCustomChunkSize, ShouldQueue, WithMapping
{
    use Exportable;

    /**
     * @return Builder|EloquentBuilder|Relation
     */
    public function query()
    {
        return Group::first()->users();
    }

    /**
     * @param  mixed  $row
     * @return array
     */
    public function map($row): array
    {
        return [
            $row->name,
            $row->email,
        ];
    }

    /**
     * @return int
     */
    public function chunkSize(): int
    {
        return 10;
    }
}
