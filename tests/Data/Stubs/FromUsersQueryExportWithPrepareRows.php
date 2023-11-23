<?php

namespace Analize\Excel\Tests\Data\Stubs;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Analize\Excel\Concerns\Exportable;
use Analize\Excel\Concerns\FromQuery;
use Analize\Excel\Concerns\WithCustomChunkSize;
use Analize\Excel\Tests\Data\Stubs\Database\User;

class FromUsersQueryExportWithPrepareRows implements FromQuery, WithCustomChunkSize
{
    use Exportable;

    /**
     * @return Builder|EloquentBuilder|Relation
     */
    public function query()
    {
        return User::query();
    }

    /**
     * @return int
     */
    public function chunkSize(): int
    {
        return 10;
    }

    /**
     * @param  iterable  $rows
     * @return iterable
     */
    public function prepareRows($rows)
    {
        return (new Collection($rows))->map(function ($user) {
            $user->name .= '_prepared_name';

            return $user;
        })->toArray();
    }
}
