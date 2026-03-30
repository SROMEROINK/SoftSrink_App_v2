<?php

namespace App\Services;

use App\Models\RegistroDeFabricacion;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class HistoricalRegistroFabricacionImportService
{
    private const CSV_PATH = 'C:\Users\SergioDanielRomero\Documents\SQL_SRINK_LARAVEL_11\CARGA EXCEL-DB\registro_de_fabricacion\registro_de_fabricacion_2018-2026.csv';
    private const LAST_MATCHING_ID = 10976;

    private const REQUIRED_HEADERS = [
        'Id_OF',
        'Nro_OF',
        'Id_Producto',
        'Nro_Parcial',
        'Nro_OF_Parcial',
        'Cant_Piezas',
        'Fecha_Fabricacion',
        'Horario',
        'Nombre_Operario',
        'Turno',
        'Cant_Horas_Extras',
    ];

    public function csvExists(): bool
    {
        return is_file(self::CSV_PATH);
    }

    public function historicalImportAlreadyApplied(): bool
    {
        return $this->pendingImportCount() === 0;
    }

    public function pendingImportCount(): int
    {
        if (!$this->csvExists()) {
            return 0;
        }

        $handle = fopen(self::CSV_PATH, 'r');
        if ($handle === false) {
            return 0;
        }

        $headers = fgetcsv($handle, 0, ';', '"', '\\');
        if ($headers === false) {
            fclose($handle);
            return 0;
        }

        $headers = $this->normalizeHeaders($headers);
        $existingKeys = RegistroDeFabricacion::query()->pluck('Nro_OF_Parcial')->all();
        $existingMap = array_fill_keys($existingKeys, true);
        $pending = 0;

        while (($data = fgetcsv($handle, 0, ';', '"', '\\')) !== false) {
            if ($this->isEmptyRow($data)) {
                continue;
            }

            $row = $this->mapRow($headers, $data);
            if ($this->toInteger($row['Id_OF'] ?? null) <= self::LAST_MATCHING_ID) {
                continue;
            }

            $key = trim((string) ($row['Nro_OF_Parcial'] ?? ''));
            if ($key === '' || isset($existingMap[$key])) {
                continue;
            }

            $existingMap[$key] = true;
            $pending++;
        }

        fclose($handle);

        return $pending;
    }

    public function importFromDefaultPath(?int $userId = null): array
    {
        if (!$this->csvExists()) {
            throw new RuntimeException('No se encontro el CSV historico de registro de fabricacion.');
        }

        $handle = fopen(self::CSV_PATH, 'r');
        if ($handle === false) {
            throw new RuntimeException('No se pudo abrir el CSV historico de registro de fabricacion.');
        }

        $headers = fgetcsv($handle, 0, ';', '"', '\\');
        if ($headers === false) {
            fclose($handle);
            throw new RuntimeException('El CSV historico de registro de fabricacion esta vacio.');
        }

        $headers = $this->normalizeHeaders($headers);
        $missingHeaders = array_diff(self::REQUIRED_HEADERS, $headers);
        if (!empty($missingHeaders)) {
            fclose($handle);
            throw new RuntimeException('El CSV historico de registro de fabricacion no tiene todas las columnas requeridas.');
        }

        $existingRows = RegistroDeFabricacion::query()
            ->get(['Nro_OF_Parcial', 'Nro_OF', 'Id_Producto', 'Nro_Parcial', 'Cant_Piezas', 'Fecha_Fabricacion', 'Horario', 'Nombre_Operario', 'Turno', 'Cant_Horas_Extras'])
            ->keyBy('Nro_OF_Parcial');

        $summary = [
            'processed' => 0,
            'created' => 0,
            'skipped_existing' => 0,
            'skipped_changed_existing' => 0,
            'skipped_before_cutoff' => 0,
            'omitted_incomplete' => 0,
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

            if ($this->isIncompleteRow($row)) {
                $summary['omitted_incomplete']++;
                $summary['omitted_rows'][] = [
                    'line' => $lineNumber,
                    'reason' => 'incomplete',
                    'nro_of_parcial' => $row['Nro_OF_Parcial'] ?? null,
                ];
                continue;
            }

            if ($this->toInteger($row['Id_OF'] ?? null) <= self::LAST_MATCHING_ID) {
                $summary['skipped_before_cutoff']++;
                continue;
            }

            $key = trim((string) $row['Nro_OF_Parcial']);
            $existing = $existingRows->get($key);

            if ($existing) {
                if ($this->rowMatchesExisting($existing, $row)) {
                    $summary['skipped_existing']++;
                } else {
                    $summary['skipped_changed_existing']++;
                    $summary['omitted_rows'][] = [
                        'line' => $lineNumber,
                        'reason' => 'changed_existing',
                        'nro_of_parcial' => $key,
                    ];
                }

                continue;
            }

            $rowsToInsert[] = [
                'Nro_OF' => $this->toInteger($row['Nro_OF']),
                'Id_Producto' => $this->toInteger($row['Id_Producto']),
                'Nro_Parcial' => $this->toInteger($row['Nro_Parcial']),
                'Nro_OF_Parcial' => $key,
                'Cant_Piezas' => $this->toInteger($row['Cant_Piezas']),
                'Fecha_Fabricacion' => $this->toDate($row['Fecha_Fabricacion']),
                'Horario' => trim((string) $row['Horario']),
                'Nombre_Operario' => $this->nullableString($row['Nombre_Operario'] ?? null),
                'Turno' => trim((string) $row['Turno']),
                'Cant_Horas_Extras' => $this->toInteger($row['Cant_Horas_Extras']),
                'reg_Status' => 1,
                'created_at' => $now,
                'created_by' => $userId,
                'updated_at' => $now,
                'updated_by' => $userId,
                'deleted_at' => null,
                'deleted_by' => null,
            ];

            $existingRows->put($key, (object) [
                'Nro_OF_Parcial' => $key,
                'Nro_OF' => $row['Nro_OF'],
                'Id_Producto' => $row['Id_Producto'],
                'Nro_Parcial' => $row['Nro_Parcial'],
                'Cant_Piezas' => $row['Cant_Piezas'],
                'Fecha_Fabricacion' => $row['Fecha_Fabricacion'],
                'Horario' => $row['Horario'],
                'Nombre_Operario' => $row['Nombre_Operario'],
                'Turno' => $row['Turno'],
                'Cant_Horas_Extras' => $row['Cant_Horas_Extras'],
            ]);

            $summary['created']++;
        }

        fclose($handle);

        foreach (array_chunk($rowsToInsert, 200) as $chunk) {
            DB::table('registro_de_fabricacion')->insert($chunk);
        }

        return $summary;
    }

    private function normalizeHeaders(array $headers): array
    {
        return array_map(function ($header) {
            $header = (string) $header;
            $header = preg_replace('/^\xEF\xBB\xBF/', '', $header);
            $header = str_replace("\xEF\xBB\xBF", '', $header);
            $header = ltrim($header, "\u{FEFF}");

            return trim($header);
        }, $headers);
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

    private function isIncompleteRow(array $row): bool
    {
        foreach (self::REQUIRED_HEADERS as $header) {
            if (!array_key_exists($header, $row)) {
                return true;
            }
        }

        return trim((string) ($row['Nro_OF_Parcial'] ?? '')) === '';
    }

    private function rowMatchesExisting($existing, array $row): bool
    {
        return (int) $existing->Nro_OF === $this->toInteger($row['Nro_OF'])
            && (int) $existing->Id_Producto === $this->toInteger($row['Id_Producto'])
            && (int) $existing->Nro_Parcial === $this->toInteger($row['Nro_Parcial'])
            && (int) $existing->Cant_Piezas === $this->toInteger($row['Cant_Piezas'])
            && $this->normalizeDateValue($existing->Fecha_Fabricacion) === $this->toDate($row['Fecha_Fabricacion'])
            && trim((string) $existing->Horario) === trim((string) $row['Horario'])
            && $this->nullableString($existing->Nombre_Operario) === $this->nullableString($row['Nombre_Operario'] ?? null)
            && trim((string) $existing->Turno) === trim((string) $row['Turno'])
            && (int) $existing->Cant_Horas_Extras === $this->toInteger($row['Cant_Horas_Extras']);
    }

    private function normalizeDateValue($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return Carbon::parse($value)->format('Y-m-d');
    }

    private function toInteger(?string $value): int
    {
        return (int) preg_replace('/\D+/', '', (string) $value);
    }

    private function toDate(?string $value): ?string
    {
        $trimmed = trim((string) $value);
        if ($trimmed === '') {
            return null;
        }

        return Carbon::parse(str_replace('/', '-', $trimmed))->format('Y-m-d');
    }

    private function nullableString($value): ?string
    {
        $trimmed = trim((string) $value);

        return $trimmed === '' ? null : $trimmed;
    }
}
