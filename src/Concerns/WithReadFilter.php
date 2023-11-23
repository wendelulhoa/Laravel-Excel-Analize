<?php

namespace Analize\Excel\Concerns;

use Analize\PhpSpreadsheet\Reader\IReadFilter;

interface WithReadFilter
{
    /**
     * @return IReadFilter
     */
    public function readFilter(): IReadFilter;
}
