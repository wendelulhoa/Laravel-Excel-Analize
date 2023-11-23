<?php

namespace Analize\Excel\Concerns;

use Analize\PhpSpreadsheet\Worksheet\Worksheet;

interface WithStyles
{
    public function styles(Worksheet $sheet);
}
