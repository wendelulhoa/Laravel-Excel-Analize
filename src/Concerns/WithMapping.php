<?php

namespace Analize\Excel\Concerns;

/**
 * @template RowType of mixed
 */
interface WithMapping
{
    /**
     * @param  RowType  $row
     * @return array
     */
    public function map($row): array;
}
