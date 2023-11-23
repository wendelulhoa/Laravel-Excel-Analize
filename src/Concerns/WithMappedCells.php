<?php

namespace Analize\Excel\Concerns;

interface WithMappedCells
{
    /**
     * @return array
     */
    public function mapping(): array;
}
