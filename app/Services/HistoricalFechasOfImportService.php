<?php

namespace App\Services;

use App\Models\FechasOf;
use App\Models\PedidoCliente;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class HistoricalFechasOfImportService
{
    private const CSV_PATH = 'C:\Users\SergioDanielRomero\Documents\SQL_SRINK_LARAVEL_11\CARGA EXCEL-DB\OF_Fechas\OF_Fechas_2026.csv';
    private const EMPTY_DATE = '9999-12-31';
    private const EMPTY_TIME = '00:00:00';

    private const REQUIRED_HEADERS = [
        'N° O.F',
        'Código de Producto',
        'N° Programa Husillo 1',
        'N° Programa Husillo 2',
        'Inicio de P.A.P',
        'Inicio de P.A.P_Hora',
        'Fin de P.A.P',
        'Fin de P.A.P_Hora',
        'Inicio de Produccion',
        'Fin de Produccion',
        'Tiempo de Pieza',
        'Cant.Seg x Pieza',
    ];

    public function csvExists(): bool
    {
        return is_file(self::CSV_PATH);
    }

    public function importFromDefaultPath(?int $userId = null): array
    {
        if (! $this->csvExists()) {
            throw new RuntimeException('No se encontro el CSV de fechas OF.');
        }

        $handle = fopen(self::CSV_PATH, 'r');
        if ($handle === false) {
            throw new RuntimeException('No se pudo abrir el CSV de fechas OF.');
        }

        $headers = fgetcsv($handle, 0, ';', '"', '\\');
        if ($headers === false) {
            fclose($handle);
            throw new RuntimeException('El CSV de fechas OF esta vacio.');
        }

        $headers = $this->normalizeHeaders($headers);
        $missingHeaders = array_diff(self::REQUIRED_HEADERS, $headers);
        if (! empty($missingHeaders)) {
            fclose($handle);
            throw new RuntimeException('El CSV de fechas OF no tiene todas las columnas requeridas.');
        }

        $pedidosByNroOf = PedidoCliente::query()
            ->whereNull('deleted_at')
            ->get(['Id_OF', 'Nro_OF'])
            ->keyBy('Nro_OF');

        $existentesByIdOf = FechasOf::query()
            ->get(['Id_Fechas', 'Id_OF', 'created_by'])
            ->keyBy('Id_OF');

        $summary = [
            'processed' => 0,
            'updated' => 0,
            'created' => 0,
            'skipped_missing_pedido' => 0,
            'invalid_rows' => 0,
            'invalid_line_numbers' => [],
        ];

        DB::beginTransaction();

        try {
            while (($data = fgetcsv($handle, 0, ';', '"', '\\')) !== false) {
                if ($this->isEmptyRow($data)) {
                    continue;
                }

                $summary['processed']++;
                $lineNumber = $summary['processed'] + 1;
                $row = $this->mapRow($headers, $data);
                $nroOf = $this->toInteger($row['N° O.F'] ?? null);

                if ($nroOf <= 0) {
                    $summary['invalid_rows']++;
                    $summary['invalid_line_numbers'][] = $lineNumber;
                    continue;
                }

                $pedido = $pedidosByNroOf->get($nroOf);
                if (! $pedido) {
                    $summary['skipped_missing_pedido']++;
                    continue;
                }

                $tiempoPieza = $this->toDecimal($row['Tiempo de Pieza'] ?? null);
                $tiempoSeg = $this->toIntegerOrNull($row['Cant.Seg x Pieza'] ?? null);

                if ($tiempoSeg === null) {
                    $tiempoSeg = $this->calculateTiempoSegFromDecimal($tiempoPieza);
                }

                $registro = $existentesByIdOf->get($pedido->Id_OF) ?? new FechasOf();

                if (! $registro->exists) {
                    $registro->Id_OF = $pedido->Id_OF;
                    $registro->created_by = $userId;
                }

                $registro->Nro_OF_fechas = $nroOf;
                $registro->Nro_Programa_H1 = $this->nullableString($row['N° Programa Husillo 1'] ?? null);
                $registro->Nro_Programa_H2 = $this->nullableString($row['N° Programa Husillo 2'] ?? null);
                $registro->Inicio_PAP = $this->toDateOrDefault($row['Inicio de P.A.P'] ?? null);
                $registro->Hora_Inicio_PAP = $this->toTimeOrDefault($row['Inicio de P.A.P_Hora'] ?? null);
                $registro->Fin_PAP = $this->toDateOrDefault($row['Fin de P.A.P'] ?? null);
                $registro->Hora_Fin_PAP = $this->toTimeOrDefault($row['Fin de P.A.P_Hora'] ?? null);
                $registro->Inicio_OF = $this->toDateOrDefault($row['Inicio de Produccion'] ?? null);
                $registro->Finalizacion_OF = $this->toDateOrDefault($row['Fin de Produccion'] ?? null);
                $registro->Tiempo_Pieza = $tiempoPieza;
                $registro->Tiempo_Seg = $tiempoSeg ?? 0;
                $registro->reg_Status = 1;
                $registro->updated_by = $userId;
                $registro->save();

                $existentesByIdOf->put($pedido->Id_OF, $registro);

                if ($registro->wasRecentlyCreated) {
                    $summary['created']++;
                } else {
                    $summary['updated']++;
                }
            }

            fclose($handle);
            DB::commit();
        } catch (\Throwable $e) {
            fclose($handle);
            DB::rollBack();
            throw $e;
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

    private function toInteger(?string $value): int
    {
        $normalized = trim((string) $value);
        if ($normalized === '') {
            return 0;
        }

        return (int) preg_replace('/[^\d\-]+/', '', $normalized);
    }

    private function toIntegerOrNull(?string $value): ?int
    {
        $normalized = trim((string) $value);
        if ($normalized === '') {
            return null;
        }

        return $this->toInteger($normalized);
    }

    private function toDecimal(?string $value): float
    {
        $normalized = str_replace(',', '.', trim((string) $value));
        if ($normalized === '') {
            return 0.0;
        }

        return (float) number_format((float) $normalized, 2, '.', '');
    }

    private function nullableString(?string $value): ?string
    {
        $normalized = trim((string) $value);

        return $normalized === '' ? null : $normalized;
    }

    private function toDateOrDefault(?string $value): string
    {
        $normalized = trim((string) $value);
        if ($normalized === '') {
            return self::EMPTY_DATE;
        }

        return Carbon::parse(str_replace('/', '-', $normalized))->format('Y-m-d');
    }

    private function toTimeOrDefault(?string $value): string
    {
        $normalized = trim((string) $value);
        if ($normalized === '') {
            return self::EMPTY_TIME;
        }

        return strlen($normalized) === 5 ? $normalized . ':00' : $normalized;
    }

    private function calculateTiempoSegFromDecimal(float $value): int
    {
        $normalized = number_format($value, 2, '.', '');
        $parts = explode('.', $normalized);
        $minutes = (int) ($parts[0] ?? 0);
        $seconds = (int) ($parts[1] ?? 0);

        return ($minutes * 60) + $seconds;
    }
}
