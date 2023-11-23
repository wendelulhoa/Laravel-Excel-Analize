<?php

namespace Analize\Excel\Concerns;

interface WithEvents
{
    /**
     * @return array
     */
    public function registerEvents(): array;
}
