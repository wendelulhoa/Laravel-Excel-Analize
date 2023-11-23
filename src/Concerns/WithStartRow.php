<?php

namespace Analize\Excel\Concerns;

interface WithStartRow
{
    /**
     * @return int
     */
    public function startRow(): int;
}
