<?php

namespace Analize\Excel\Tests\Data\Stubs;

use Exception;
use Illuminate\Database\Eloquent\Collection;
use Analize\Excel\Concerns\Exportable;
use Analize\Excel\Concerns\FromCollection;
use Analize\Excel\Concerns\WithMapping;
use Analize\Excel\Tests\Data\Stubs\Database\User;
use PHPUnit\Framework\Assert;

class QueuedExportWithFailedHook implements FromCollection, WithMapping
{
    use Exportable;

    /**
     * @var bool
     */
    public $failed = false;

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
        throw new Exception('we expect this');
    }

    /**
     * @param  Exception  $exception
     */
    public function failed(Exception $exception)
    {
        Assert::assertEquals('we expect this', $exception->getMessage());

        app()->bind('queue-has-failed', function () {
            return true;
        });
    }
}
