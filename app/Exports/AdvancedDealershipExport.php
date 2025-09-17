<?php

declare(strict_types=1);

namespace App\Exports;

use Illuminate\Database\Eloquent\Collection;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class AdvancedDealershipExport extends ExcelExport
{
    public array $exportData = [];

    public function __construct()
    {
        parent::__construct('dealerships');
    }

    public function setExportData(array $data): void
    {
        $this->exportData = $data;
    }

    public function collection()
    {
        return collect();
    }
}
