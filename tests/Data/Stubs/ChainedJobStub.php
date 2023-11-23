<?php

namespace Analize\Excel\Tests\Data\Stubs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class ChainedJobStub implements ShouldQueue
{
    use Queueable;
}
