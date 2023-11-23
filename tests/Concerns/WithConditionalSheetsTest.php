<?php

namespace Analize\Excel\Tests\Concerns;

use Analize\Excel\Concerns\Importable;
use Analize\Excel\Concerns\ToArray;
use Analize\Excel\Concerns\WithConditionalSheets;
use Analize\Excel\Concerns\WithMultipleSheets;
use Analize\Excel\Tests\TestCase;

class WithConditionalSheetsTest extends TestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->withFactories(__DIR__ . '/../Data/Stubs/Database/Factories');
    }

    /**
     * @test
     */
    public function can_select_which_sheets_will_be_imported()
    {
        $import = new class implements WithMultipleSheets
        {
            use Importable, WithConditionalSheets;

            public $sheets = [];

            public function __construct()
            {
                $this->init();
            }

            public function init()
            {
                $this->sheets = [
                    'Sheet1' => new class implements ToArray
                    {
                        public $called = false;

                        public function array(array $array)
                        {
                            $this->called = true;
                        }
                    },
                    'Sheet2' => new class implements ToArray
                    {
                        public $called = false;

                        public function array(array $array)
                        {
                            $this->called = true;
                        }
                    },
                ];
            }

            /**
             * @return array
             */
            public function conditionalSheets(): array
            {
                return $this->sheets;
            }
        };

        $import->onlySheets('Sheet1')->import('import-multiple-sheets.xlsx');
        $this->assertTrue($import->sheets['Sheet1']->called);
        $this->assertFalse($import->sheets['Sheet2']->called);

        $import->init();

        $import->onlySheets('Sheet2')->import('import-multiple-sheets.xlsx');
        $this->assertTrue($import->sheets['Sheet2']->called);
        $this->assertFalse($import->sheets['Sheet1']->called);

        $import->init();

        $import->onlySheets(['Sheet1', 'Sheet2'])->import('import-multiple-sheets.xlsx');
        $this->assertTrue($import->sheets['Sheet1']->called);
        $this->assertTrue($import->sheets['Sheet2']->called);

        $import->init();

        $import->onlySheets('Sheet1', 'Sheet2')->import('import-multiple-sheets.xlsx');
        $this->assertTrue($import->sheets['Sheet1']->called);
        $this->assertTrue($import->sheets['Sheet2']->called);
    }
}
