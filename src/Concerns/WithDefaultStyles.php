<?php

namespace Analize\Excel\Concerns;

use Analize\PhpSpreadsheet\Style\Style;

interface WithDefaultStyles
{
    /**
     * @return array|void
     */
    public function defaultStyles(Style $defaultStyle);
}
