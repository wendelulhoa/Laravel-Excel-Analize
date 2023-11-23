<?php

namespace Analize\Excel\Concerns;

use Analize\Excel\Validators\Failure;

interface SkipsOnFailure
{
    /**
     * @param  Failure[]  $failures
     */
    public function onFailure(Failure ...$failures);
}
