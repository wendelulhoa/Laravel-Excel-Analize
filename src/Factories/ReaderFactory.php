<?php

namespace Analize\Excel\Factories;

use Analize\Excel\Concerns\MapsCsvSettings;
use Analize\Excel\Concerns\WithCustomCsvSettings;
use Analize\Excel\Concerns\WithLimit;
use Analize\Excel\Concerns\WithReadFilter;
use Analize\Excel\Concerns\WithStartRow;
use Analize\Excel\Exceptions\NoTypeDetectedException;
use Analize\Excel\Files\TemporaryFile;
use Analize\Excel\Filters\LimitFilter;
use Analize\PhpSpreadsheet\IOFactory;
use Analize\PhpSpreadsheet\Reader\Csv;
use Analize\PhpSpreadsheet\Reader\Exception;
use Analize\PhpSpreadsheet\Reader\IReader;

class ReaderFactory
{
    use MapsCsvSettings;

    /**
     * @param  object  $import
     * @param  TemporaryFile  $file
     * @param  string  $readerType
     * @return IReader
     *
     * @throws Exception
     */
    public static function make($import, TemporaryFile $file, string $readerType = null): IReader
    {
        $reader = IOFactory::createReader(
            $readerType ?: static::identify($file)
        );

        if (method_exists($reader, 'setReadDataOnly')) {
            $reader->setReadDataOnly(config('excelAnalize.imports.read_only', true));
        }

        if (method_exists($reader, 'setReadEmptyCells')) {
            $reader->setReadEmptyCells(!config('excelAnalize.imports.ignore_empty', false));
        }

        if ($reader instanceof Csv) {
            static::applyCsvSettings(config('excelAnalize.imports.csv', []));

            if ($import instanceof WithCustomCsvSettings) {
                static::applyCsvSettings($import->getCsvSettings());
            }

            $reader->setDelimiter(static::$delimiter);
            $reader->setEnclosure(static::$enclosure);
            $reader->setEscapeCharacter(static::$escapeCharacter);
            $reader->setContiguous(static::$contiguous);
            $reader->setInputEncoding(static::$inputEncoding);
            if (method_exists($reader, 'setTestAutoDetect')) {
                $reader->setTestAutoDetect(static::$testAutoDetect);
            }
        }

        if ($import instanceof WithReadFilter) {
            $reader->setReadFilter($import->readFilter());
        } elseif ($import instanceof WithLimit) {
            $reader->setReadFilter(new LimitFilter(
                $import instanceof WithStartRow ? $import->startRow() : 1,
                $import->limit()
            ));
        }

        return $reader;
    }

    /**
     * @param  TemporaryFile  $temporaryFile
     * @return string
     *
     * @throws NoTypeDetectedException
     */
    private static function identify(TemporaryFile $temporaryFile): string
    {
        try {
            return IOFactory::identify($temporaryFile->getLocalPath());
        } catch (Exception $e) {
            throw new NoTypeDetectedException(null, null, $e);
        }
    }
}
