<?php

namespace Analize\Excel\Concerns;

interface WithLimit
{
    /**
     * @return int
     */
    public function limit(): int;
}
