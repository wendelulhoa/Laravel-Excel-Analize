<?php

namespace Analize\Excel\Tests;

use Illuminate\Queue\InteractsWithQueue;
use Analize\Excel\Jobs\AppendDataToSheet;
use Analize\Excel\Jobs\AppendQueryToSheet;
use Analize\Excel\Jobs\AppendViewToSheet;
use Analize\Excel\Jobs\ReadChunk;

class InteractsWithQueueTest extends TestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @test
     */
    public function read_chunk_job_can_interact_with_queue()
    {
        $this->assertContains(InteractsWithQueue::class, class_uses(ReadChunk::class));
    }

    /**
     * @test
     */
    public function append_data_to_sheet_job_can_interact_with_queue()
    {
        $this->assertContains(InteractsWithQueue::class, class_uses(AppendDataToSheet::class));
    }

    /**
     * @test
     */
    public function append_query_to_sheet_job_can_interact_with_queue()
    {
        $this->assertContains(InteractsWithQueue::class, class_uses(AppendQueryToSheet::class));
    }

    /**
     * @test
     */
    public function append_view_to_sheet_job_can_interact_with_queue()
    {
        $this->assertContains(InteractsWithQueue::class, class_uses(AppendViewToSheet::class));
    }
}
