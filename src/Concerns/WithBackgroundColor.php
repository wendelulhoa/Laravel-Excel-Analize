<?php

namespace Analize\Excel\Concerns;

use Analize\PhpSpreadsheet\Style\Color;

interface WithBackgroundColor
{
    /**
     * @return string|array|Color
     */
    public function backgroundColor();
}
