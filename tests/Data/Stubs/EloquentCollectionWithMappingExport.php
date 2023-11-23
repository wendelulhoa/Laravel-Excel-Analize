<?php

namespace Analize\Excel\Tests\Data\Stubs;

use Illuminate\Database\Eloquent\Collection;
use Analize\Excel\Concerns\Exportable;
use Analize\Excel\Concerns\FromCollection;
use Analize\Excel\Concerns\WithMapping;
use Analize\Excel\Tests\Data\Stubs\Database\User;

class EloquentCollectionWithMappingExport implements FromCollection, WithMapping
{
    use Exportable;

    /**
     * @return Collection
     */
    public function collection()
    {
        return collect([
            new User([
                'firstname' => 'Patrick',
                'lastname'  => 'Brouwers',
            ]),
        ]);
    }

    /**
     * @param  User  $user
     * @return array
     */
    public function map($user): array
    {
        return [
            $user->firstname,
            $user->lastname,
        ];
    }
}
