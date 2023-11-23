<?php

namespace Analize\Excel\Factories;

use Analize\Excel\Cache\CacheManager;
use Analize\Excel\Concerns\MapsCsvSettings;
use Analize\Excel\Concerns\WithCharts;
use Analize\Excel\Concerns\WithCustomCsvSettings;
use Analize\Excel\Concerns\WithMultipleSheets;
use Analize\Excel\Concerns\WithPreCalculateFormulas;
use Analize\PhpSpreadsheet\IOFactory;
use Analize\PhpSpreadsheet\Spreadsheet;
use Analize\PhpSpreadsheet\Writer\Csv;
use Analize\PhpSpreadsheet\Writer\Html;
use Analize\PhpSpreadsheet\Writer\IWriter;

class WriterFactory
{
    use MapsCsvSettings;

    /**
     * @param  string  $writerType
     * @param  Spreadsheet  $spreadsheet
     * @param  object  $export
     * @return IWriter
     *
     * @throws \Analize\PhpSpreadsheet\Writer\Exception
     */
    public static function make(string $writerType, Spreadsheet $spreadsheet, $export): IWriter
    {
        $writer = IOFactory::createWriter($spreadsheet, $writerType);

        $writer->setUseDiskCaching(
            config('excel.cache.driver', CacheManager::DRIVER_MEMORY) !== CacheManager::DRIVER_MEMORY
        );

        if (static::includesCharts($export)) {
            $writer->setIncludeCharts(true);
        }

        if ($writer instanceof Html && $export instanceof WithMultipleSheets) {
            $writer->writeAllSheets();
        }

        if ($writer instanceof Csv) {
            static::applyCsvSettings(config('excel.exports.csv', []));

            if ($export instanceof WithCustomCsvSettings) {
                static::applyCsvSettings($export->getCsvSettings());
            }

            $writer->setDelimiter(static::$delimiter);
            $writer->setEnclosure(static::$enclosure);
            $writer->setEnclosureRequired((bool) static::$enclosure);
            $writer->setLineEnding(static::$lineEnding);
            $writer->setUseBOM(static::$useBom);
            $writer->setIncludeSeparatorLine(static::$includeSeparatorLine);
            $writer->setExcelCompatibility(static::$excelCompatibility);
            $writer->setOutputEncoding(static::$outputEncoding);
        }

        // Calculation settings
        $writer->setPreCalculateFormulas(
            $export instanceof WithPreCalculateFormulas
                ? true
                : config('excel.exports.pre_calculate_formulas', false)
        );

        return $writer;
    }

    /**
     * @param  $export
     * @return bool
     */
    private static function includesCharts($export): bool
    {
        if ($export instanceof WithCharts) {
            return true;
        }

        if ($export instanceof WithMultipleSheets) {
            foreach ($export->sheets() as $sheet) {
                if ($sheet instanceof WithCharts) {
                    return true;
                }
            }
        }

        return false;
    }
}
