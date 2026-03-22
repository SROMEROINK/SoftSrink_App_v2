<?php

namespace App\Services;

use App\Models\MpEgreso;
use App\Models\PedidoCliente;
use App\Models\PedidoClienteMp;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class HistoricalPedidoMpEgresoImportService
{
    private const CSV_PATH = 'C:\Users\SergioDanielRomero\Documents\SQL_SRINK_LARAVEL_11\CARGA EXCEL-DB\MP\egresos_calidad_entregas.csv';

    private const REQUIRED_HEADERS = [
        'N° O.F',
        'N° De Ingreso MP',
        'Cant. de Barras MP',
        'Cant. de barras preparadas',
        'Longitud x Un.(MP)',
        'Fecha de Planificación',
        'Resp. de Planificación',
        'Pedido de Material N°',
        'Fecha de Entrega',
        'Resp. de Entrega',
    ];

    public function csvExists(): bool
    {
        return is_file(self::CSV_PATH);
    }


    public function historicalImportAlreadyApplied(): bool
    {
        return MpEgreso::whereBetween('Nro_Pedido_MP', [1, 500])
            ->whereNull('deleted_at')
            ->count() >= 1099;
    }

    public function importFromDefaultPath(?int $userId = null): array
    {
        $this->ensurePlanningColumns();

        if (!$this->csvExists()) {
            throw new RuntimeException('No se encontro el CSV historico de egresos de calidad.');
        }

        $handle = fopen(self::CSV_PATH, 'r');
        if ($handle === false) {
            throw new RuntimeException('No se pudo abrir el CSV historico de egresos de calidad.');
        }

        $headers = fgetcsv($handle, 0, ';');
        if ($headers === false) {
            fclose($handle);
            throw new RuntimeException('El CSV historico esta vacio.');
        }

        $headers = array_map(fn ($header) => trim((string) preg_replace('/^\xEF\xBB\xBF/', '', (string) $header)), $headers);
        $missingHeaders = array_diff(self::REQUIRED_HEADERS, $headers);
        if (!empty($missingHeaders)) {
            fclose($handle);
            throw new RuntimeException('El CSV historico no tiene todas las columnas requeridas.');
        }

        $summary = [
            'processed' => 0,
            'omitted_incomplete' => 0,
            'omitted_missing_of' => 0,
            'created_pedido_mp' => 0,
            'updated_pedido_mp' => 0,
            'created_egreso' => 0,
            'updated_egreso' => 0,
            'unchanged' => 0,
            'omitted_rows' => [],
        ];

        while (($data = fgetcsv($handle, 0, ';')) !== false) {
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
                    'of' => $row['N° O.F'] ?? null,
                ];
                continue;
            }

            $pedido = PedidoCliente::where('Nro_OF', $this->toInteger($row['N° O.F']))
                ->whereNull('deleted_at')
                ->first();

            if (!$pedido) {
                $summary['omitted_missing_of']++;
                $summary['omitted_rows'][] = [
                    'line' => $lineNumber,
                    'reason' => 'missing_of',
                    'of' => $row['N° O.F'] ?? null,
                ];
                continue;
            }

            $ingreso = DB::table('mp_ingreso as i')
                ->leftJoin('mp_materia_prima as mp', 'mp.Id_Materia_Prima', '=', 'i.Id_Materia_Prima')
                ->leftJoin('mp_diametro as d', 'd.Id_Diametro', '=', 'i.Id_Diametro_MP')
                ->whereNull('i.deleted_at')
                ->where('i.Nro_Ingreso_MP', $this->toInteger($row['N° De Ingreso MP']))
                ->select([
                    'i.Nro_Ingreso_MP',
                    'i.Codigo_MP',
                    'i.Nro_Certificado_MP',
                    'mp.Nombre_Materia as Materia_Prima',
                    'd.Valor_Diametro as Diametro_MP',
                ])
                ->first();

            $pedidoMp = PedidoClienteMp::withTrashed()->firstOrNew(['Id_OF' => $pedido->Id_OF]);
            if ($pedidoMp->trashed()) {
                $pedidoMp->restore();
            }

            $pedidoWasNew = !$pedidoMp->exists;

            $pedidoMp->fill([
                'Estado_Plani_Id' => $pedidoMp->Estado_Plani_Id ?: 11,
                'Nro_Ingreso_MP' => $this->toInteger($row['N° De Ingreso MP']),
                'Pedido_Material_Nro' => trim((string) $row['Pedido de Material N°']),
                'Cant_Barras_MP' => $this->toInteger($row['Cant. de Barras MP']),
                'Longitud_Un_MP' => $this->toDecimal($row['Longitud x Un.(MP)']),
                'Fecha_Planificacion' => $this->toDate($row['Fecha de Planificación']),
                'Responsable_Planificacion' => trim((string) $row['Resp. de Planificación']),
                'Codigo_MP' => $pedidoMp->Codigo_MP ?: ($ingreso->Codigo_MP ?? null),
                'Materia_Prima' => $pedidoMp->Materia_Prima ?: ($ingreso->Materia_Prima ?? null),
                'Diametro_MP' => $pedidoMp->Diametro_MP ?: ($ingreso->Diametro_MP ?? null),
                'Nro_Certificado_MP' => $pedidoMp->Nro_Certificado_MP ?: ($ingreso->Nro_Certificado_MP ?? null),
                'Observaciones' => $pedidoMp->Observaciones ?: 'Importado desde CSV historico de pedidos y entregas de MP.',
                'reg_Status' => 1,
            ]);

            if ($pedidoWasNew) {
                $pedidoMp->created_by = $userId;
                $pedidoMp->updated_by = $userId;
                $pedidoMp->save();
                $summary['created_pedido_mp']++;
            } elseif ($pedidoMp->isDirty()) {
                $pedidoMp->updated_by = $userId;
                $pedidoMp->save();
                $summary['updated_pedido_mp']++;
            }

            $cantidadSolicitada = $this->toInteger($row['Cant. de Barras MP']);
            $cantidadPreparada = $this->toInteger($row['Cant. de barras preparadas']);
            $longitudUnidad = $this->toDecimal($row['Longitud x Un.(MP)']);

            $egreso = MpEgreso::withTrashed()->firstOrNew(['Id_OF_Salidas_MP' => $pedido->Id_OF]);
            if ($egreso->trashed()) {
                $egreso->restore();
            }

            $egresoWasNew = !$egreso->exists;

            $egreso->fill([
                'Cantidad_Unidades_MP' => $cantidadSolicitada,
                'Cantidad_Unidades_MP_Preparadas' => $cantidadPreparada,
                'Cantidad_MP_Adicionales' => 0,
                'Cant_Devoluciones' => 0,
                'Total_Salidas_MP' => $cantidadPreparada,
                'Total_Mtros_Utilizados' => round($cantidadPreparada * $longitudUnidad, 2),
                'Fecha_del_Pedido_Produccion' => $this->toDate($row['Fecha de Planificación']),
                'Responsable_Pedido_Produccion' => trim((string) $row['Resp. de Planificación']),
                'Nro_Pedido_MP' => $this->toInteger($row['Pedido de Material N°']),
                'Fecha_de_Entrega_Pedido_Calidad' => $this->toDate($row['Fecha de Entrega']),
                'Responsable_de_entrega_Calidad' => trim((string) $row['Resp. de Entrega']),
                'reg_Status' => 1,
            ]);

            if ($egresoWasNew) {
                $egreso->created_by = $userId;
                $egreso->updated_by = $userId;
                $egreso->save();
                $summary['created_egreso']++;
            } elseif ($egreso->isDirty()) {
                $egreso->updated_by = $userId;
                $egreso->save();
                $summary['updated_egreso']++;
            } else {
                $summary['unchanged']++;
            }
        }

        fclose($handle);

        return $summary;
    }

    private function ensurePlanningColumns(): void
    {
        if (!Schema::hasColumn('pedido_cliente_mp', 'Fecha_Planificacion')) {
            DB::statement("ALTER TABLE pedido_cliente_mp ADD COLUMN Fecha_Planificacion DATE NULL AFTER Cant_Barras_MP");
        }

        if (!Schema::hasColumn('pedido_cliente_mp', 'Responsable_Planificacion')) {
            DB::statement("ALTER TABLE pedido_cliente_mp ADD COLUMN Responsable_Planificacion VARCHAR(255) NULL AFTER Fecha_Planificacion");
        }
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
            if (trim((string) ($row[$header] ?? '')) === '') {
                return true;
            }
        }

        return false;
    }

    private function toInteger(string $value): int
    {
        return (int) preg_replace('/\D+/', '', $value);
    }

    private function toDecimal(string $value): float
    {
        $normalized = str_replace('.', '', trim($value));
        $normalized = str_replace(',', '.', $normalized);

        return (float) $normalized;
    }

    private function toDate(string $value): ?string
    {
        $trimmed = trim($value);
        return $trimmed === '' ? null : $trimmed;
    }
}
