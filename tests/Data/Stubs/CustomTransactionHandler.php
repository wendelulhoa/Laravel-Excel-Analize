<?php

namespace Analize\Excel\Tests\Data\Stubs;

use Analize\Excel\Transactions\TransactionHandler;

class CustomTransactionHandler implements TransactionHandler
{
    public function __invoke(callable $callback)
    {
        return $callback();
    }
}
