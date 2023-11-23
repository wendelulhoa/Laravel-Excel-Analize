<?php

namespace Analize\Excel\Concerns;

use Analize\Excel\Row;

interface OnEachRow
{
    /**
     * @param  Row  $row
     */
    public function onRow(Row $row);
}
