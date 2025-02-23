<?php

namespace Analize\Excel\Events;

use Analize\Excel\Reader;

class BeforeImport extends Event
{
    /**
     * @var Reader
     */
    public $reader;

    /**
     * @param  Reader  $reader
     * @param  object  $importable
     */
    public function __construct(Reader $reader, $importable)
    {
        $this->reader     = $reader;
        parent::__construct($importable);
    }

    /**
     * @return Reader
     */
    public function getReader(): Reader
    {
        return $this->reader;
    }

    /**
     * @return mixed
     */
    public function getDelegate()
    {
        return $this->reader;
    }
}
