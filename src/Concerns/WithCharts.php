<?php

namespace Analize\Excel\Concerns;

use Analize\PhpSpreadsheet\Chart\Chart;

interface WithCharts
{
    /**
     * @return Chart|Chart[]
     */
    public function charts();
}
