<?php

namespace App\Services;

use App\Models\MpIngreso;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class HistoricalMpSalidaInicialImportService
{
    private const CSV_PATH = 'C:\Users\SergioDanielRomero\Documents\SQL_SRINK_LARAVEL_11\CARGA EXCEL-DB\MP\salidas_iniciales_mp.csv';

    private const REQUIRED_HEADERS = [
        'stock inicial',
        'devoluciones al proveedor',
        'diferencia de stock',
        'salidas_final',
        'mts. totales',
    ];

    public function csvExists(): bool
    {
        return is_file(self::CSV_PATH);
    }

    public function replaceFromDefaultPath(?int $userId = null): array
    {
        if (!$this->csvExists()) {
            throw new RuntimeException('No se encontro el CSV historico de salidas iniciales.');
        }

        $handle = fopen(self::CSV_PATH, 'r');
        if ($handle === false) {
            throw new RuntimeException('No se pudo abrir el CSV historico de salidas iniciales.');
        }

        $headers = fgetcsv($handle, 0, ';', '"', '\\');
        if ($headers === false) {
            fclose($handle);
            throw new RuntimeException('El CSV historico de salidas iniciales esta vacio.');
        }

        $headers = array_map([$this, 'normalizeHeader'], $headers);
        $missingHeaders = array_diff(self::REQUIRED_HEADERS, $headers);
        $hasIngresoHeader = in_array('no de ingreso', $headers, true) || in_array('nº de ingreso', $headers, true);
        if (!$hasIngresoHeader || !empty($missingHeaders)) {
            fclose($handle);
            throw new RuntimeException('El CSV historico de salidas iniciales no tiene todas las columnas requeridas.');
        }

        $ingresos = MpIngreso::query()
            ->whereNull('deleted_at')
            ->whereIn('Id_MP', function ($query) {
                $query->from('mp_ingreso')
                    ->selectRaw('MAX(Id_MP)')
                    ->whereNull('deleted_at')
                    ->groupBy('Nro_Ingreso_MP');
            })
            ->get(['Id_MP', 'Nro_Ingreso_MP', 'Longitud_Unidad_MP'])
            ->keyBy(fn (MpIngreso $ingreso) => (int) $ingreso->Nro_Ingreso_MP);

        $summary = [
            'processed' => 0,
            'reloaded' => 0,
            'omitted_missing_ingreso' => 0,
            'omitted_rows' => [],
        ];

        $now = Carbon::now();
        $rowsToInsert = [];

        while (($data = fgetcsv($handle, 0, ';', '"', '\\')) !== false) {
            if ($this->isEmptyRow($data)) {
                continue;
            }

            $summary['processed']++;
            $lineNumber = $summary['processed'] + 1;
            $row = $this->mapRow($headers, $data);
            $nroIngreso = $this->toInteger($row['no de ingreso'] ?? ($row['nº de ingreso'] ?? ''));
            $ingreso = $ingresos->get($nroIngreso);

            if (!$ingreso) {
                $summary['omitted_missing_ingreso']++;
                $summary['omitted_rows'][] = [
                    'line' => $lineNumber,
                    'nro_ingreso' => $nroIngreso,
                    'reason' => 'missing_ingreso',
                ];
                continue;
            }

            $stockInicial = $this->toInteger($row['stock inicial'] ?? '');
            $devolucionesProveedor = $this->toInteger($row['devoluciones al proveedor'] ?? '');
            $ajusteStock = $this->toInteger($row['diferencia de stock'] ?? '');
            $totalSalidasCsv = trim((string) ($row['salidas_final'] ?? ''));
            $totalMtsCsv = trim((string) ($row['mts. totales'] ?? ''));
            $totalSalidas = $totalSalidasCsv !== ''
                ? $this->toInteger($totalSalidasCsv)
                : ($stockInicial - $devolucionesProveedor + $ajusteStock);
            $totalMtsUtilizados = $totalMtsCsv !== ''
                ? $this->toDecimal($totalMtsCsv)
                : round($totalSalidas * (float) ($ingreso->Longitud_Unidad_MP ?? 0), 2);

            $rowsToInsert[] = [
                'Id_Ingreso_MP' => (int) $ingreso->Id_MP,
                'Stock_Inicial' => $stockInicial,
                'Devoluciones_Proveedor' => $devolucionesProveedor,
                'Ajuste_Stock' => $ajusteStock,
                'Total_Salidas_MP' => $totalSalidas,
                'Total_mm_Utilizados' => $totalMtsUtilizados,
                'reg_Status' => 1,
                'created_at' => $now,
                'created_by' => $userId,
                'updated_at' => $now,
                'updated_by' => $userId,
                'deleted_at' => null,
                'deleted_by' => null,
            ];

            $summary['reloaded']++;
        }

        fclose($handle);

        DB::table('mp_salidas_iniciales')->delete();

        foreach (array_chunk($rowsToInsert, 200) as $chunk) {
            DB::table('mp_salidas_iniciales')->insert($chunk);
        }

        return $summary;
    }

    private function normalizeHeader(string $header): string
    {
        $header = trim((string) preg_replace('/^\xEF\xBB\xBF/u', '', $header));
        $header = str_replace(['º', '°', 'ª', 'Âº', 'Â°', 'Âª', 'Ã‚Âº', 'Ã‚Â°', 'Ã‚Âª'], ['o', 'o', 'a', 'o', 'o', 'a', 'o', 'o', 'a'], $header);
        $header = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $header) ?: $header;
        $header = strtolower(trim($header));
        $header = preg_replace('/\s+/', ' ', $header);

        return $header;
    }

    private function mapRow(array $headers, array $data): array
    {
        $row = [];
        foreach ($headers as $index => $header) {
            $row[$header] = trim((string) ($data[$index] ?? ''));
        }

        return $row;
    }

    private function isEmptyRow(array $data): bool
    {
        foreach ($data as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    private function toInteger(string $value): int
    {
        $normalized = trim($value);
        if ($normalized === '') {
            return 0;
        }

        return (int) str_replace(['.', ','], '', $normalized);
    }

    private function toDecimal(string $value): float
    {
        $normalized = trim($value);
        if ($normalized === '') {
            return 0.0;
        }

        $normalized = str_replace('.', '', $normalized);
        $normalized = str_replace(',', '.', $normalized);

        return (float) $normalized;
    }
}