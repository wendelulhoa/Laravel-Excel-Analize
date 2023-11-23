<?php

namespace Analize\Excel\Tests\Concerns;

use Illuminate\Database\Eloquent\Model;
use Analize\Excel\Concerns\HasReferencesToOtherSheets;
use Analize\Excel\Concerns\Importable;
use Analize\Excel\Concerns\SkipsEmptyRows;
use Analize\Excel\Concerns\ToArray;
use Analize\Excel\Concerns\ToModel;
use Analize\Excel\Concerns\WithCalculatedFormulas;
use Analize\Excel\Concerns\WithMultipleSheets;
use Analize\Excel\Concerns\WithStartRow;
use Analize\Excel\Tests\TestCase;
use PHPUnit\Framework\Assert;

class WithCalculatedFormulasTest extends TestCase
{
    /**
     * @test
     */
    public function by_default_does_not_calculate_formulas()
    {
        $import = new class implements ToArray
        {
            use Importable;

            public $called = false;

            /**
             * @param  array  $array
             */
            public function array(array $array)
            {
                $this->called = true;

                Assert::assertSame('=1+1', $array[0][0]);
            }
        };

        $import->import('import-formulas.xlsx');

        $this->assertTrue($import->called);
    }

    /**
     * @test
     */
    public function can_import_to_array_with_calculated_formulas()
    {
        $import = new class implements ToArray, WithCalculatedFormulas
        {
            use Importable;

            public $called = false;

            /**
             * @param  array  $array
             */
            public function array(array $array)
            {
                $this->called = true;

                Assert::assertSame(2, $array[0][0]);
            }
        };

        $import->import('import-formulas.xlsx');

        $this->assertTrue($import->called);
    }

    /**
     * @test
     */
    public function can_import_to_model_with_calculated_formulas()
    {
        $import = new class implements ToModel, WithCalculatedFormulas
        {
            use Importable;

            public $called = false;

            /**
             * @param  array  $row
             * @return Model|null
             */
            public function model(array $row)
            {
                $this->called = true;

                Assert::assertSame(2, $row[0]);

                return null;
            }
        };

        $import->import('import-formulas.xlsx');

        $this->assertTrue($import->called);
    }

    public function can_import_with_formulas_and_reference()
    {
        $import = new class implements ToModel, WithCalculatedFormulas, WithStartRow
        {
            use Importable;

            public $called = false;

            /**
             * @param  array  $row
             * @return Model|null
             */
            public function model(array $row)
            {
                $this->called = true;

                Assert::assertSame('julien', $row[1]);

                return null;
            }

            public function startRow(): int
            {
                return 2;
            }
        };

        $import->import('import-external-reference.xls');

        $this->assertTrue($import->called);
    }

    /**
     * @test
     */
    public function can_import_to_array_with_calculated_formulas_and_multi_sheet_references()
    {
        $import = new class implements WithMultipleSheets, HasReferencesToOtherSheets
        {
            use Importable;

            public $test = 'test1';

            public function sheets(): array
            {
                return [
                    new class implements ToArray, HasReferencesToOtherSheets
                    {
                        public $test = 'test2';

                        public function array(array $array)
                        {
                            Assert::assertEquals([
                                ['1', '1'],
                            ], $array);
                        }
                    },
                    new class implements ToArray, WithCalculatedFormulas, HasReferencesToOtherSheets
                    {
                        public $test = 'test2';

                        public function array(array $array)
                        {
                            Assert::assertEquals([
                                ['2'],
                            ], $array);
                        }
                    },
                ];
            }
        };

        $import->import('import-formulas-multiple-sheets.xlsx');
    }

    /**
     * @test
     */
    public function can_import_to_array_with_calculated_formulas_and_skips_empty()
    {
        $import = new class implements ToArray, WithCalculatedFormulas, SkipsEmptyRows
        {
            use Importable;

            public $called = false;

            /**
             * @param  array  $array
             */
            public function array(array $array)
            {
                $this->called = true;

                Assert::assertSame(2, $array[0][0]);
            }
        };

        $import->import('import-formulas.xlsx');

        $this->assertTrue($import->called);
    }

    /**
     * @test
     */
    public function can_import_to_model_with_calculated_formulas_and_skips_empty()
    {
        $import = new class implements ToModel, WithCalculatedFormulas, SkipsEmptyRows
        {
            use Importable;

            public $called = false;

            /**
             * @param  array  $row
             * @return Model|null
             */
            public function model(array $row)
            {
                $this->called = true;

                Assert::assertSame(2, $row[0]);

                return null;
            }
        };

        $import->import('import-formulas.xlsx');

        $this->assertTrue($import->called);
    }
}
