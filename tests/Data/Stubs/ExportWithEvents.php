<?php

namespace Analize\Excel\Tests\Data\Stubs;

use Analize\Excel\Concerns\Exportable;
use Analize\Excel\Concerns\WithEvents;
use Analize\Excel\Events\AfterSheet;
use Analize\Excel\Events\BeforeExport;
use Analize\Excel\Events\BeforeSheet;
use Analize\Excel\Events\BeforeWriting;

class ExportWithEvents implements WithEvents
{
    use Exportable;

    /**
     * @var callable
     */
    public $beforeExport;

    /**
     * @var callable
     */
    public $beforeWriting;

    /**
     * @var callable
     */
    public $beforeSheet;

    /**
     * @var callable
     */
    public $afterSheet;

    /**
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            BeforeExport::class  => $this->beforeExport ?? function () {
            },
            BeforeWriting::class => $this->beforeWriting ?? function () {
            },
            BeforeSheet::class   => $this->beforeSheet ?? function () {
            },
            AfterSheet::class    => $this->afterSheet ?? function () {
            },
        ];
    }
}
