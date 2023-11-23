<?php

namespace Analize\Excel\Concerns;

use Analize\PhpSpreadsheet\Worksheet\BaseDrawing;

interface WithDrawings
{
    /**
     * @return BaseDrawing|BaseDrawing[]
     */
    public function drawings();
}
