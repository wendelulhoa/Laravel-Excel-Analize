<?php

namespace Analize\Excel;

use Analize\PhpSpreadsheet\Calculation\Exception;
use Analize\PhpSpreadsheet\Cell\Cell as SpreadsheetCell;
use Analize\PhpSpreadsheet\RichText\RichText;
use Analize\PhpSpreadsheet\Style\NumberFormat;
use Analize\PhpSpreadsheet\Worksheet\Worksheet;

/** @mixin SpreadsheetCell */
class Cell
{
    use DelegatedMacroable;

    /**
     * @var SpreadsheetCell
     */
    private $cell;

    /**
     * @param  SpreadsheetCell  $cell
     */
    public function __construct(SpreadsheetCell $cell)
    {
        $this->cell = $cell;
    }

    /**
     * @param  Worksheet  $worksheet
     * @param  string  $coordinate
     * @return Cell
     *
     * @throws \Analize\PhpSpreadsheet\Exception
     */
    public static function make(Worksheet $worksheet, string $coordinate)
    {
        return new static($worksheet->getCell($coordinate));
    }

    /**
     * @return SpreadsheetCell
     */
    public function getDelegate(): SpreadsheetCell
    {
        return $this->cell;
    }

    /**
     * @param  null  $nullValue
     * @param  bool  $calculateFormulas
     * @param  bool  $formatData
     * @return mixed
     */
    public function getValue($nullValue = null, $calculateFormulas = false, $formatData = true)
    {
        $value = $nullValue;
        if ($this->cell->getValue() !== null) {
            if ($this->cell->getValue() instanceof RichText) {
                $value = $this->cell->getValue()->getPlainText();
            } elseif ($calculateFormulas) {
                try {
                    $value = $this->cell->getCalculatedValue();
                } catch (Exception $e) {
                    $value = $this->cell->getOldCalculatedValue();
                }
            } else {
                $value = $this->cell->getValue();
            }

            if ($formatData) {
                $style = $this->cell->getWorksheet()->getParent()->getCellXfByIndex($this->cell->getXfIndex());
                $value = NumberFormat::toFormattedString(
                    $value,
                    ($style && $style->getNumberFormat()) ? $style->getNumberFormat()->getFormatCode() : NumberFormat::FORMAT_GENERAL
                );
            }
        }

        return $value;
    }
}
