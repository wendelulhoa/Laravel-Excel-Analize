<?php

namespace Analize\Excel\Tests\Data\Stubs;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder;
use Analize\Excel\Concerns\Exportable;
use Analize\Excel\Concerns\FromQuery;
use Analize\Excel\Concerns\WithEvents;
use Analize\Excel\Concerns\WithMapping;
use Analize\Excel\Events\BeforeSheet;
use Analize\Excel\Tests\Data\Stubs\Database\User;

class FromUsersQueryExportWithMapping implements FromQuery, WithMapping, WithEvents
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
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            BeforeSheet::class   => function (BeforeSheet $event) {
                $event->sheet->chunkSize(10);
            },
        ];
    }

    /**
     * @param  User  $row
     * @return array
     */
    public function map($row): array
    {
        return [
            'name' => $row->name,
        ];
    }
}
