<?php

namespace Analize\Excel\Tests\Data\Stubs;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Analize\Excel\Concerns\Exportable;
use Analize\Excel\Concerns\FromQuery;
use Analize\Excel\Concerns\WithCustomChunkSize;

class FromNonEloquentQueryExport implements FromQuery, WithCustomChunkSize
{
    use Exportable;

    /**
     * @return Builder|EloquentBuilder|Relation
     */
    public function query()
    {
        return DB::table('users')->select('name')->orderBy('id');
    }

    /**
     * @return int
     */
    public function chunkSize(): int
    {
        return 10;
    }
}
