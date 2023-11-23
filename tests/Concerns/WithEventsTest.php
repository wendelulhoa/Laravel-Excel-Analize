<?php

namespace Analize\Excel\Tests\Concerns;

use Analize\Excel\Concerns\Exportable;
use Analize\Excel\Events\AfterBatch;
use Analize\Excel\Events\AfterChunk;
use Analize\Excel\Events\AfterImport;
use Analize\Excel\Events\AfterSheet;
use Analize\Excel\Events\BeforeExport;
use Analize\Excel\Events\BeforeImport;
use Analize\Excel\Events\BeforeSheet;
use Analize\Excel\Events\BeforeWriting;
use Analize\Excel\Excel;
use Analize\Excel\Reader;
use Analize\Excel\Sheet;
use Analize\Excel\Tests\Data\Stubs\BeforeExportListener;
use Analize\Excel\Tests\Data\Stubs\CustomConcern;
use Analize\Excel\Tests\Data\Stubs\CustomSheetConcern;
use Analize\Excel\Tests\Data\Stubs\ExportWithEvents;
use Analize\Excel\Tests\Data\Stubs\ImportWithEvents;
use Analize\Excel\Tests\Data\Stubs\ImportWithEventsChunksAndBatches;
use Analize\Excel\Tests\TestCase;
use Analize\Excel\Writer;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class WithEventsTest extends TestCase
{
    /**
     * @test
     */
    public function export_events_get_called()
    {
        $event = new ExportWithEvents();

        $eventsTriggered = 0;

        $event->beforeExport = function ($event) use (&$eventsTriggered) {
            $this->assertInstanceOf(BeforeExport::class, $event);
            $this->assertInstanceOf(Writer::class, $event->getWriter());
            $eventsTriggered++;
        };

        $event->beforeWriting = function ($event) use (&$eventsTriggered) {
            $this->assertInstanceOf(BeforeWriting::class, $event);
            $this->assertInstanceOf(Writer::class, $event->getWriter());
            $eventsTriggered++;
        };

        $event->beforeSheet = function ($event) use (&$eventsTriggered) {
            $this->assertInstanceOf(BeforeSheet::class, $event);
            $this->assertInstanceOf(Sheet::class, $event->getSheet());
            $eventsTriggered++;
        };

        $event->afterSheet = function ($event) use (&$eventsTriggered) {
            $this->assertInstanceOf(AfterSheet::class, $event);
            $this->assertInstanceOf(Sheet::class, $event->getSheet());
            $eventsTriggered++;
        };

        $this->assertInstanceOf(BinaryFileResponse::class, $event->download('filename.xlsx'));
        $this->assertEquals(4, $eventsTriggered);
    }

    /**
     * @test
     */
    public function import_events_get_called()
    {
        $import = new ImportWithEvents();

        $eventsTriggered = 0;

        $import->beforeImport = function ($event) use (&$eventsTriggered) {
            $this->assertInstanceOf(BeforeImport::class, $event);
            $this->assertInstanceOf(Reader::class, $event->getReader());
            $eventsTriggered++;
        };

        $import->afterImport = function ($event) use (&$eventsTriggered) {
            $this->assertInstanceOf(AfterImport::class, $event);
            $this->assertInstanceOf(Reader::class, $event->getReader());
            $eventsTriggered++;
        };

        $import->beforeSheet = function ($event) use (&$eventsTriggered) {
            $this->assertInstanceOf(BeforeSheet::class, $event);
            $this->assertInstanceOf(Sheet::class, $event->getSheet());
            $eventsTriggered++;
        };

        $import->afterSheet = function ($event) use (&$eventsTriggered) {
            $this->assertInstanceOf(AfterSheet::class, $event);
            $this->assertInstanceOf(Sheet::class, $event->getSheet());
            $eventsTriggered++;
        };

        $import->import('import.xlsx');
        $this->assertEquals(4, $eventsTriggered);
    }

    /**
     * @test
     */
    public function import_chunked_events_get_called()
    {
        $import = new ImportWithEventsChunksAndBatches();

        $beforeImport = 0;
        $afterImport  = 0;
        $beforeSheet  = 0;
        $afterSheet   = 0;
        $afterBatch   = 0;
        $afterChunk   = 0;

        $import->beforeImport = function (BeforeImport $event) use (&$beforeImport) {
            $this->assertInstanceOf(Reader::class, $event->getReader());
            // Ensure event is fired only once
            $this->assertEquals(0, $beforeImport, 'Before import called twice');
            $beforeImport++;
        };

        $import->afterImport = function (AfterImport $event) use (&$afterImport) {
            $this->assertInstanceOf(Reader::class, $event->getReader());
            $this->assertEquals(0, $afterImport, 'After import called twice');
            $afterImport++;
        };

        $import->beforeSheet = function (BeforeSheet $event) use (&$beforeSheet) {
            $this->assertInstanceOf(Sheet::class, $event->getSheet());
            $beforeSheet++;
        };

        $import->afterSheet = function (AfterSheet $event) use (&$afterSheet) {
            $this->assertInstanceOf(Sheet::class, $event->getSheet());
            $afterSheet++;
        };

        $import->afterBatch = function (AfterBatch $event) use ($import, &$afterBatch) {
            $this->assertEquals(
                $import->batchSize(),
                $event->getBatchSize(),
                'Wrong Batch size'
            );
            $this->assertEquals(
                $afterBatch * $import->batchSize() + 1,
                $event->getStartRow(),
                'Wrong batch start row');
            $afterBatch++;
        };

        $import->afterChunk = function (AfterChunk $event) use ($import, &$afterChunk) {
            $this->assertEquals(
                $event->getStartRow(),
                $afterChunk * $import->chunkSize() + 1,
                'Wrong chunk start row');
            $afterChunk++;
        };

        $import->import('import-batches.xlsx');
        $this->assertEquals(10, $afterSheet);
        $this->assertEquals(10, $beforeSheet);
        $this->assertEquals(50, $afterBatch);
        $this->assertEquals(10, $afterChunk);
    }

    /**
     * @test
     */
    public function can_have_invokable_class_as_listener()
    {
        $event = new ExportWithEvents();

        $event->beforeExport = new BeforeExportListener(function ($event) {
            $this->assertInstanceOf(BeforeExport::class, $event);
            $this->assertInstanceOf(Writer::class, $event->getWriter());
        });

        $this->assertInstanceOf(BinaryFileResponse::class, $event->download('filename.xlsx'));
    }

    /**
     * @test
     */
    public function can_have_global_event_listeners()
    {
        $event = new class
        {
            use Exportable;
        };

        $beforeExport = false;
        Writer::listen(BeforeExport::class, function () use (&$beforeExport) {
            $beforeExport = true;
        });

        $beforeWriting = false;
        Writer::listen(BeforeWriting::class, function () use (&$beforeWriting) {
            $beforeWriting = true;
        });

        $beforeSheet = false;
        Sheet::listen(BeforeSheet::class, function () use (&$beforeSheet) {
            $beforeSheet = true;
        });

        $afterSheet = false;
        Sheet::listen(AfterSheet::class, function () use (&$afterSheet) {
            $afterSheet = true;
        });

        $this->assertInstanceOf(BinaryFileResponse::class, $event->download('filename.xlsx'));

        $this->assertTrue($beforeExport, 'Before export event not triggered');
        $this->assertTrue($beforeWriting, 'Before writing event not triggered');
        $this->assertTrue($beforeSheet, 'Before sheet event not triggered');
        $this->assertTrue($afterSheet, 'After sheet event not triggered');
    }

    /**
     * @test
     */
    public function can_have_custom_concern_handlers()
    {
        // Add a custom concern handler for the given concern.
        Excel::extend(CustomConcern::class, function (CustomConcern $exportable, Writer $writer) {
            $writer->getSheetByIndex(0)->append(
                $exportable->custom()
            );
        });

        $exportWithConcern = new class implements CustomConcern
        {
            use Exportable;

            public function custom()
            {
                return [
                    ['a', 'b'],
                ];
            }
        };

        $exportWithConcern->store('with-custom-concern.xlsx');
        $actual = $this->readAsArray(__DIR__ . '/../Data/Disks/Local/with-custom-concern.xlsx', 'Xlsx');
        $this->assertEquals([
            ['a', 'b'],
        ], $actual);

        $exportWithoutConcern = new class
        {
            use Exportable;
        };

        $exportWithoutConcern->store('without-custom-concern.xlsx');
        $actual = $this->readAsArray(__DIR__ . '/../Data/Disks/Local/without-custom-concern.xlsx', 'Xlsx');

        $this->assertEquals([[null]], $actual);
    }

    /**
     * @test
     */
    public function can_have_custom_sheet_concern_handlers()
    {
        // Add a custom concern handler for the given concern.
        Excel::extend(CustomSheetConcern::class, function (CustomSheetConcern $exportable, Sheet $sheet) {
            $sheet->append(
                $exportable->custom()
            );
        }, AfterSheet::class);

        $exportWithConcern = new class implements CustomSheetConcern
        {
            use Exportable;

            public function custom()
            {
                return [
                    ['c', 'd'],
                ];
            }
        };

        $exportWithConcern->store('with-custom-concern.xlsx');
        $actual = $this->readAsArray(__DIR__ . '/../Data/Disks/Local/with-custom-concern.xlsx', 'Xlsx');
        $this->assertEquals([
            ['c', 'd'],
        ], $actual);

        $exportWithoutConcern = new class
        {
            use Exportable;
        };

        $exportWithoutConcern->store('without-custom-concern.xlsx');
        $actual = $this->readAsArray(__DIR__ . '/../Data/Disks/Local/without-custom-concern.xlsx', 'Xlsx');

        $this->assertEquals([[null]], $actual);
    }
}
