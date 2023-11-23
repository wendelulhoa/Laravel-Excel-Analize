<?php

namespace Analize\Excel;

use Illuminate\Support\Collection;
use Analize\Excel\Concerns\ToArray;
use Analize\Excel\Concerns\ToCollection;
use Analize\Excel\Concerns\ToModel;
use Analize\Excel\Concerns\WithCalculatedFormulas;
use Analize\Excel\Concerns\WithFormatData;
use Analize\Excel\Concerns\WithMappedCells;
use Analize\PhpSpreadsheet\Worksheet\Worksheet;

class MappedReader
{
    /**
     * @param  WithMappedCells  $import
     * @param  Worksheet  $worksheet
     *
     * @throws \Analize\PhpSpreadsheet\Exception
     */
    public function map(WithMappedCells $import, Worksheet $worksheet)
    {
        $mapped = $import->mapping();
        array_walk_recursive($mapped, function (&$coordinate) use ($import, $worksheet) {
            $cell = Cell::make($worksheet, $coordinate);

            $coordinate = $cell->getValue(
                null,
                $import instanceof WithCalculatedFormulas,
                $import instanceof WithFormatData
            );
        });

        if ($import instanceof ToModel) {
            $model = $import->model($mapped);

            if ($model) {
                $model->saveOrFail();
            }
        }

        if ($import instanceof ToCollection) {
            $import->collection(new Collection($mapped));
        }

        if ($import instanceof ToArray) {
            $import->array($mapped);
        }
    }
}
