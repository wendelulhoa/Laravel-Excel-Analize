<?php

namespace Analize\Excel\Tests\Data\Stubs;

use Illuminate\Support\Collection;
use Analize\Excel\Concerns\Exportable;
use Analize\Excel\Concerns\FromCollection;
use Analize\Excel\Concerns\RegistersEventListeners;
use Analize\Excel\Concerns\ShouldAutoSize;
use Analize\Excel\Concerns\WithEvents;
use Analize\Excel\Concerns\WithTitle;
use Analize\Excel\Events\BeforeWriting;
use Analize\Excel\Tests\TestCase;
use Analize\Excel\Writer;

class SheetWith100Rows implements FromCollection, WithTitle, ShouldAutoSize, WithEvents
{
    use Exportable, RegistersEventListeners;

    /**
     * @var string
     */
    private $title;

    /**
     * @param  string  $title
     */
    public function __construct(string $title)
    {
        $this->title = $title;
    }

    /**
     * @return Collection
     */
    public function collection()
    {
        $collection = new Collection;
        for ($i = 0; $i < 100; $i++) {
            $row = new Collection();
            for ($j = 0; $j < 5; $j++) {
                $row[] = $this->title() . '-' . $i . '-' . $j;
            }

            $collection->push($row);
        }

        return $collection;
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return $this->title;
    }

    /**
     * @param  BeforeWriting  $event
     */
    public static function beforeWriting(BeforeWriting $event)
    {
        TestCase::assertInstanceOf(Writer::class, $event->writer);
    }
}
