<?php

namespace Analize\Excel\Concerns;

interface WithUpserts
{
    /**
     * @return string|array
     */
    public function uniqueBy();
}
