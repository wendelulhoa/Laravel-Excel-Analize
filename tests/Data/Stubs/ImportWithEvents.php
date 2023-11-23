<?php

namespace Analize\Excel\Tests\Data\Stubs;

use Analize\Excel\Concerns\Importable;
use Analize\Excel\Concerns\WithEvents;
use Analize\Excel\Events\AfterImport;
use Analize\Excel\Events\AfterSheet;
use Analize\Excel\Events\BeforeImport;
use Analize\Excel\Events\BeforeSheet;

class ImportWithEvents implements WithEvents
{
    use Importable;

    /**
     * @var callable
     */
    public $beforeImport;

    /**
     * @var callable
     */
    public $afterImport;

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
            BeforeImport::class => $this->beforeImport ?? function () {
            },
            AfterImport::class => $this->afterImport ?? function () {
            },
            BeforeSheet::class => $this->beforeSheet ?? function () {
            },
            AfterSheet::class => $this->afterSheet ?? function () {
            },
        ];
    }
}
