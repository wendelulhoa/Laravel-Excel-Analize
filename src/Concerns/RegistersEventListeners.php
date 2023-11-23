<?php

namespace Analize\Excel\Concerns;

use Analize\Excel\Events\AfterBatch;
use Analize\Excel\Events\AfterChunk;
use Analize\Excel\Events\AfterImport;
use Analize\Excel\Events\AfterSheet;
use Analize\Excel\Events\BeforeExport;
use Analize\Excel\Events\BeforeImport;
use Analize\Excel\Events\BeforeSheet;
use Analize\Excel\Events\BeforeWriting;
use Analize\Excel\Events\ImportFailed;

trait RegistersEventListeners
{
    /**
     * @return array
     */
    public function registerEvents(): array
    {
        $listenersClasses = [
            BeforeExport::class  => 'beforeExport',
            BeforeWriting::class => 'beforeWriting',
            BeforeImport::class  => 'beforeImport',
            AfterImport::class   => 'afterImport',
            AfterBatch::class    => 'afterBatch',
            AfterChunk::class    => 'afterChunk',
            ImportFailed::class  => 'importFailed',
            BeforeSheet::class   => 'beforeSheet',
            AfterSheet::class    => 'afterSheet',
        ];
        $listeners = [];

        foreach ($listenersClasses as $class => $name) {
            // Method names are case insensitive in php
            if (method_exists($this, $name)) {
                // Allow methods to not be static
                $listeners[$class] = [$this, $name];
            }
        }

        return $listeners;
    }
}
