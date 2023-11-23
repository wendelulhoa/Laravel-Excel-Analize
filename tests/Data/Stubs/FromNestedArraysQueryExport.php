<?php

namespace Analize\Excel\Tests\Data\Stubs;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder;
use Analize\Excel\Concerns\Exportable;
use Analize\Excel\Concerns\FromQuery;
use Analize\Excel\Concerns\WithMapping;
use Analize\Excel\Tests\Data\Stubs\Database\Group;

class FromNestedArraysQueryExport implements FromQuery, WithMapping
{
    use Exportable;

    /**
     * @return Builder|EloquentBuilder|Relation
     */
    public function query()
    {
        $query = Group::with('users');

        return $query;
    }

    /**
     * @param  Group  $row
     * @return array
     */
    public function map($row): array
    {
        $rows    = [];
        $sub_row = [$row->name, ''];
        $count   = 0;

        foreach ($row->users as $user) {
            if ($count === 0) {
                $sub_row[1] = $user['email'];
            } else {
                $sub_row = ['', $user['email']];
            }

            $rows[] = $sub_row;
            $count++;
        }

        if ($count === 0) {
            $rows[] = $sub_row;
        }

        return $rows;
    }
}
