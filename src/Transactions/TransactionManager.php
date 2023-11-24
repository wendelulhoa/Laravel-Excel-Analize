<?php

namespace Analize\Excel\Transactions;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Manager;

class TransactionManager extends Manager
{
    /**
     * @return string
     */
    public function getDefaultDriver()
    {
        return config('excelAnalize.transactions.handler');
    }

    /**
     * @return NullTransactionHandler
     */
    public function createNullDriver()
    {
        return new NullTransactionHandler();
    }

    /**
     * @return DbTransactionHandler
     */
    public function createDbDriver()
    {
        return new DbTransactionHandler(
            DB::connection(config('excelAnalize.transactions.db.connection'))
        );
    }
}
