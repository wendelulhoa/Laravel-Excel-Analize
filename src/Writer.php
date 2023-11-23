<?php

namespace Analize\Excel;

use Analize\Excel\Concerns\WithBackgroundColor;
use Analize\Excel\Concerns\WithCustomValueBinder;
use Analize\Excel\Concerns\WithDefaultStyles;
use Analize\Excel\Concerns\WithEvents;
use Analize\Excel\Concerns\WithMultipleSheets;
use Analize\Excel\Concerns\WithProperties;
use Analize\Excel\Concerns\WithTitle;
use Analize\Excel\Events\BeforeExport;
use Analize\Excel\Events\BeforeWriting;
use Analize\Excel\Factories\WriterFactory;
use Analize\Excel\Files\RemoteTemporaryFile;
use Analize\Excel\Files\TemporaryFile;
use Analize\Excel\Files\TemporaryFileFactory;
use Analize\PhpSpreadsheet\Cell\Cell;
use Analize\PhpSpreadsheet\IOFactory;
use Analize\PhpSpreadsheet\Spreadsheet;
use Analize\PhpSpreadsheet\Style\Color;
use Analize\PhpSpreadsheet\Style\Fill;

/** @mixin Spreadsheet */
class Writer
{
    use DelegatedMacroable, HasEventBus;

    /**
     * @var Spreadsheet
     */
    protected $spreadsheet;

    /**
     * @var object
     */
    protected $exportable;

    /**
     * @var TemporaryFileFactory
     */
    protected $temporaryFileFactory;

    /**
     * @param  TemporaryFileFactory  $temporaryFileFactory
     */
    public function __construct(TemporaryFileFactory $temporaryFileFactory)
    {
        $this->temporaryFileFactory = $temporaryFileFactory;

        $this->setDefaultValueBinder();
    }

    /**
     * @param  object  $export
     * @param  string  $writerType
     * @return TemporaryFile
     *
     * @throws \Analize\PhpSpreadsheet\Exception
     */
    public function export($export, string $writerType): TemporaryFile
    {
        $this->open($export);

        $sheetExports = [$export];
        if ($export instanceof WithMultipleSheets) {
            $sheetExports = $export->sheets();
        }

        foreach ($sheetExports as $sheetExport) {
            $this->addNewSheet()->export($sheetExport);
        }

        return $this->write($export, $this->temporaryFileFactory->makeLocal(null, strtolower($writerType)), $writerType);
    }

    /**
     * @param  object  $export
     * @return $this
     */
    public function open($export)
    {
        $this->exportable = $export;

        if ($export instanceof WithEvents) {
            $this->registerListeners($export->registerEvents());
        }

        $this->exportable  = $export;
        $this->spreadsheet = new Spreadsheet;
        $this->spreadsheet->disconnectWorksheets();

        if ($export instanceof WithCustomValueBinder) {
            Cell::setValueBinder($export);
        }

        $this->handleDocumentProperties($export);

        if ($export instanceof WithBackgroundColor) {
            $defaultStyle    = $this->spreadsheet->getDefaultStyle();
            $backgroundColor = $export->backgroundColor();

            if (is_string($backgroundColor)) {
                $defaultStyle->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($backgroundColor);
            }

            if (is_array($backgroundColor)) {
                $defaultStyle->applyFromArray(['fill' => $backgroundColor]);
            }

            if ($backgroundColor instanceof Color) {
                $defaultStyle->getFill()->setFillType(Fill::FILL_SOLID)->setStartColor($backgroundColor);
            }
        }

        if ($export instanceof WithDefaultStyles) {
            $defaultStyle = $this->spreadsheet->getDefaultStyle();
            $styles       = $export->defaultStyles($defaultStyle);

            if (is_array($styles)) {
                $defaultStyle->applyFromArray($styles);
            }
        }

        $this->raise(new BeforeExport($this, $this->exportable));

        return $this;
    }

    /**
     * @param  TemporaryFile  $tempFile
     * @param  string  $writerType
     * @return Writer
     *
     * @throws \Analize\PhpSpreadsheet\Reader\Exception
     */
    public function reopen(TemporaryFile $tempFile, string $writerType)
    {
        $reader            = IOFactory::createReader($writerType);
        $this->spreadsheet = $reader->load($tempFile->sync()->getLocalPath());

        return $this;
    }

    /**
     * @param  object  $export
     * @param  TemporaryFile  $temporaryFile
     * @param  string  $writerType
     * @return TemporaryFile
     *
     * @throws \Analize\PhpSpreadsheet\Writer\Exception
     * @throws \Analize\PhpSpreadsheet\Exception
     */
    public function write($export, TemporaryFile $temporaryFile, string $writerType): TemporaryFile
    {
        $this->exportable = $export;

        $this->spreadsheet->setActiveSheetIndex(0);

        $this->raise(new BeforeWriting($this, $this->exportable));

        $writer = WriterFactory::make(
            $writerType,
            $this->spreadsheet,
            $export
        );

        $writer->save(
            $temporaryFile->getLocalPath()
        );

        if ($temporaryFile instanceof RemoteTemporaryFile) {
            $temporaryFile->updateRemote();
            $temporaryFile->deleteLocalCopy();
        }

        $this->clearListeners();
        $this->spreadsheet->disconnectWorksheets();
        unset($this->spreadsheet);

        return $temporaryFile;
    }

    /**
     * @param  int|null  $sheetIndex
     * @return Sheet
     *
     * @throws \Analize\PhpSpreadsheet\Exception
     */
    public function addNewSheet(int $sheetIndex = null)
    {
        return new Sheet($this->spreadsheet->createSheet($sheetIndex));
    }

    /**
     * @return Spreadsheet
     */
    public function getDelegate()
    {
        return $this->spreadsheet;
    }

    /**
     * @return $this
     */
    public function setDefaultValueBinder()
    {
        Cell::setValueBinder(
            app(config('excel.value_binder.default', DefaultValueBinder::class))
        );

        return $this;
    }

    /**
     * @param  int  $sheetIndex
     * @return Sheet
     *
     * @throws \Analize\PhpSpreadsheet\Exception
     */
    public function getSheetByIndex(int $sheetIndex)
    {
        return new Sheet($this->getDelegate()->getSheet($sheetIndex));
    }

    /**
     * @param  string  $concern
     * @return bool
     */
    public function hasConcern($concern): bool
    {
        return $this->exportable instanceof $concern;
    }

    /**
     * @param  object  $export
     */
    protected function handleDocumentProperties($export)
    {
        $properties = config('excel.exports.properties', []);

        if ($export instanceof WithProperties) {
            $properties = array_merge($properties, $export->properties());
        }

        if ($export instanceof WithTitle) {
            $properties = array_merge($properties, ['title' => $export->title()]);
        }

        $props = $this->spreadsheet->getProperties();

        foreach (array_filter($properties) as $property => $value) {
            switch ($property) {
                case 'title':
                    $props->setTitle($value);
                    break;
                case 'description':
                    $props->setDescription($value);
                    break;
                case 'creator':
                    $props->setCreator($value);
                    break;
                case 'lastModifiedBy':
                    $props->setLastModifiedBy($value);
                    break;
                case 'subject':
                    $props->setSubject($value);
                    break;
                case 'keywords':
                    $props->setKeywords($value);
                    break;
                case 'category':
                    $props->setCategory($value);
                    break;
                case 'manager':
                    $props->setManager($value);
                    break;
                case 'company':
                    $props->setCompany($value);
                    break;
            }
        }
    }
}
