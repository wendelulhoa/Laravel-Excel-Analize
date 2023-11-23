<?php

namespace Analize\Excel\Tests\Data\Stubs;

use Analize\Excel\Concerns\Exportable;
use Analize\Excel\Concerns\WithTitle;

class WithTitleExport implements WithTitle
{
    use Exportable;

    /**
     * @return string
     */
    public function title(): string
    {
        return 'given-title';
    }
}
