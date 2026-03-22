<?php

namespace App\Services;

use App\Models\MpIngreso;
use App\Models\PedidoCliente;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PedidoClienteMpPlannerService
{
    public function buildForPedido(PedidoCliente $pedido, array $input = []): array
    {
        $producto = $pedido->producto;

        $productoMaterial = $this->normalizeText($producto->Prod_Material_MP ?? null);
        $productoDiametro = $this->normalizeText($producto->Prod_Diametro_de_MP ?? null);
        $productoCodigoMp = $this->normalizeText($producto->Prod_Codigo_MP ?? null);

        $materiaPrima = $this->normalizeText($input['Materia_Prima'] ?? $productoMaterial);
        $diametroMp = $this->normalizeText($input['Diametro_MP'] ?? $productoDiametro);
        $codigoMp = $this->normalizeText($input['Codigo_MP'] ?? null);

        if (!$codigoMp && $materiaPrima && $diametroMp) {
            $codigoMp = $materiaPrima . '_' . $diametroMp;
        }

        $largoPieza = $this->asFloat($input['Largo_Pieza'] ?? $producto->Prod_Longitud_de_Pieza ?? 0);
        $frenteado = $this->asFloat($input['Frenteado'] ?? 0.50);
        $anchoCutOff = $this->asFloat($input['Ancho_Cut_Off'] ?? 1.00);
        $sobrematerial = $this->asFloat($input['Sobrematerial_Promedio'] ?? 0.50);
        $cantidadFabricacion = (int) ($pedido->Cant_Fabricacion ?? 0);

        $maquina = $this->resolveMachine($input['Id_Maquina'] ?? null);
        $scrapMaquina = (float) ($maquina['scrap_maquina'] ?? $this->asFloat($input['Scrap_Maquina'] ?? 0));

        $largoTotalPieza = $largoPieza + $frenteado + $anchoCutOff + $sobrematerial;
        $mmTotales = $cantidadFabricacion > 0 && $largoTotalPieza > 0
            ? $cantidadFabricacion * $largoTotalPieza
            : 0.0;
        $metrosTotales = $mmTotales > 0 ? ($mmTotales / 1000) : 0.0;

        $ingresos = $this->getCompatibleIngresos($codigoMp, $materiaPrima, $diametroMp);
        $longitudUnMp = $this->resolveLongitudUnidad($input['Longitud_Un_MP'] ?? null, $ingresos);
        $longitudBarraSinScrap = max(0, ($longitudUnMp * 1000) - $scrapMaquina);
        $cantBarrasRequeridas = $mmTotales > 0 && $longitudBarraSinScrap > 0
            ? (int) ceil($mmTotales / $longitudBarraSinScrap)
            : 0;
        $cantPiezasPorBarra = $largoTotalPieza > 0 && $longitudBarraSinScrap > 0
            ? floor(($longitudBarraSinScrap / $largoTotalPieza) * 100) / 100
            : 0.0;

        $stockSummary = $this->buildStockSummary($ingresos, $mmTotales, $cantBarrasRequeridas, $scrapMaquina, $longitudUnMp);

        return [
            'producto' => [
                'id' => $producto->Id_Producto ?? null,
                'codigo' => $producto->Prod_Codigo ?? null,
                'descripcion' => $producto->Prod_Descripcion ?? null,
                'categoria' => $producto->categoria->Nombre_Categoria ?? null,
                'subcategoria' => $producto->subCategoria->Nombre_SubCategoria ?? null,
                'material_mp' => $productoMaterial,
                'diametro_mp' => $productoDiametro,
                'codigo_mp' => $productoCodigoMp,
                'largo_pieza' => $this->roundValue($producto->Prod_Longitud_de_Pieza ?? null),
            ],
            'seleccion' => [
                'materia_prima' => $materiaPrima,
                'diametro_mp' => $diametroMp,
                'codigo_mp' => $codigoMp,
                'largo_pieza' => $this->roundValue($largoPieza),
                'frenteado' => $this->roundValue($frenteado),
                'ancho_cut_off' => $this->roundValue($anchoCutOff),
                'sobrematerial_promedio' => $this->roundValue($sobrematerial),
                'largo_total_pieza' => $this->roundValue($largoTotalPieza),
                'mm_totales' => $this->roundValue($mmTotales),
                'metros_totales' => $this->roundValue($metrosTotales),
                'longitud_un_mp' => $this->roundValue($longitudUnMp),
                'longitud_barra_sin_scrap' => $this->roundValue($longitudBarraSinScrap),
                'cant_barras_requeridas' => $cantBarrasRequeridas,
                'cant_piezas_por_barra' => $this->roundValue($cantPiezasPorBarra),
            ],
            'maquina' => $maquina,
            'compatibilidad' => [
                'bloqueada' => (bool) ($productoCodigoMp || ($productoMaterial && $productoDiametro)),
                'requiere_codigo_producto' => (bool) $productoCodigoMp,
                'mensaje' => $this->buildCompatibilityMessage($productoCodigoMp, $productoMaterial, $productoDiametro),
            ],
            'ingresos' => $ingresos->values()->all(),
            'stock' => $stockSummary,
        ];
    }

    protected function resolveMachine($idMaquina): array
    {
        if (!$idMaquina) {
            return [
                'id_maquina' => null,
                'nro_maquina' => null,
                'familia_maquina' => null,
                'scrap_maquina' => 0.0,
            ];
        }

        $machine = DB::table('maquinas_produc')
            ->select('id_maquina', 'Nro_maquina', 'familia_maquina', 'scrap_maquina')
            ->where('id_maquina', $idMaquina)
            ->where('Status', 1)
            ->first();

        if (!$machine) {
            return [
                'id_maquina' => null,
                'nro_maquina' => null,
                'familia_maquina' => null,
                'scrap_maquina' => 0.0,
            ];
        }

        return [
            'id_maquina' => (int) $machine->id_maquina,
            'nro_maquina' => $machine->Nro_maquina,
            'familia_maquina' => $machine->familia_maquina,
            'scrap_maquina' => $this->roundValue($machine->scrap_maquina ?? 0),
        ];
    }

    protected function getCompatibleIngresos(?string $codigoMp, ?string $materiaPrima, ?string $diametroMp): Collection
    {
        $query = MpIngreso::query()
            ->leftJoin('mp_materia_prima', 'mp_ingreso.Id_Materia_Prima', '=', 'mp_materia_prima.Id_Materia_Prima')
            ->leftJoin('mp_diametro', 'mp_ingreso.Id_Diametro_MP', '=', 'mp_diametro.Id_Diametro')
            ->whereNull('mp_ingreso.deleted_at')
            ->where('mp_ingreso.reg_Status', 1)
            ->select([
                'mp_ingreso.Id_MP',
                'mp_ingreso.Nro_Ingreso_MP',
                'mp_ingreso.Codigo_MP',
                'mp_ingreso.Nro_Pedido',
                'mp_ingreso.Nro_Certificado_MP',
                'mp_ingreso.Unidades_MP',
                'mp_ingreso.Longitud_Unidad_MP',
                'mp_ingreso.Mts_Totales',
                'mp_materia_prima.Nombre_Materia as Materia_Prima',
                'mp_diametro.Valor_Diametro as Diametro_MP',
            ]);

        if ($materiaPrima) {
            $query->where('mp_materia_prima.Nombre_Materia', $materiaPrima);
        }

        return $query
            ->orderBy('mp_ingreso.Unidades_MP')
            ->orderBy('mp_ingreso.Nro_Ingreso_MP')
            ->get()
            ->filter(function ($ingreso) use ($diametroMp, $codigoMp, $materiaPrima) {
                $diametroIngreso = $this->extractDiameter($ingreso->Diametro_MP ?? $ingreso->Codigo_MP ?? null);
                $diametroRequerido = $this->extractDiameter($diametroMp ?? $codigoMp);
                $materiaIngreso = $this->normalizeText($ingreso->Materia_Prima ?? null);
                $materiaRequerida = $this->normalizeText($materiaPrima);

                if ($materiaRequerida && $materiaIngreso !== $materiaRequerida) {
                    return false;
                }

                if ($diametroRequerido === null) {
                    return true;
                }

                if ($diametroIngreso === null) {
                    return false;
                }

                return $diametroIngreso >= $diametroRequerido;
            })
            ->map(function ($ingreso) {
                return [
                    'Id_MP' => (int) $ingreso->Id_MP,
                    'Nro_Ingreso_MP' => (int) $ingreso->Nro_Ingreso_MP,
                    'Pedido_Material_Nro' => blank($ingreso->Nro_Pedido) ? null : $ingreso->Nro_Pedido,
                    'Codigo_MP' => $ingreso->Codigo_MP,
                    'Nro_Certificado_MP' => $ingreso->Nro_Certificado_MP,
                    'Unidades_MP' => (int) $ingreso->Unidades_MP,
                    'Longitud_Unidad_MP' => $this->roundValue($ingreso->Longitud_Unidad_MP),
                    'Mts_Totales' => $this->roundValue($ingreso->Mts_Totales),
                    'Materia_Prima' => $ingreso->Materia_Prima,
                    'Diametro_MP' => $ingreso->Diametro_MP,
                ];
            })
            ->values();
    }

    protected function resolveLongitudUnidad($currentValue, Collection $ingresos): float
    {
        $currentValue = $this->asFloat($currentValue);
        if ($currentValue > 0) {
            return $currentValue;
        }

        if ($ingresos->isEmpty()) {
            return 0.0;
        }

        return round((float) $ingresos->avg('Longitud_Unidad_MP'), 2);
    }

    protected function buildStockSummary(Collection $ingresos, float $mmTotales, int $cantBarrasRequeridas, float $scrapMaquina, float $longitudUnMp): array
    {
        $metrosRequeridos = $mmTotales > 0 ? ($mmTotales / 1000) : 0.0;
        $totalBarrasDisponibles = (int) $ingresos->sum('Unidades_MP');
        $totalMetrosDisponibles = (float) $ingresos->sum('Mts_Totales');
        $longitudBarraSinScrap = max(0, ($longitudUnMp * 1000) - $scrapMaquina);

        $restantes = $cantBarrasRequeridas;
        $sugerencia = [];

        foreach ($ingresos as $ingreso) {
            if ($restantes <= 0) {
                break;
            }

            $usar = min($restantes, (int) $ingreso['Unidades_MP']);
            if ($usar <= 0) {
                continue;
            }

            $sugerencia[] = [
                'Nro_Ingreso_MP' => $ingreso['Nro_Ingreso_MP'],
                'barras_a_usar' => $usar,
                'barras_remanentes' => max(0, (int) $ingreso['Unidades_MP'] - $usar),
                'Longitud_Unidad_MP' => $ingreso['Longitud_Unidad_MP'],
                'Mts_Aprox_Asignados' => $this->roundValue($usar * (float) $ingreso['Longitud_Unidad_MP']),
            ];

            $restantes -= $usar;
        }

        return [
            'cant_barras_requeridas' => $cantBarrasRequeridas,
            'metros_requeridos' => $this->roundValue($metrosRequeridos),
            'total_barras_disponibles' => $totalBarrasDisponibles,
            'total_metros_disponibles' => $this->roundValue($totalMetrosDisponibles),
            'faltan_barras' => max(0, $cantBarrasRequeridas - $totalBarrasDisponibles),
            'faltan_metros' => $this->roundValue(max(0, $metrosRequeridos - $totalMetrosDisponibles)),
            'scrap_maquina' => $this->roundValue($scrapMaquina),
            'longitud_un_mp' => $this->roundValue($longitudUnMp),
            'longitud_barra_sin_scrap' => $this->roundValue($longitudBarraSinScrap),
            'sugerencia' => $sugerencia,
        ];
    }

    protected function buildCompatibilityMessage(?string $codigoMp, ?string $materiaPrima, ?string $diametroMp): string
    {
        if ($codigoMp) {
            return "El producto ya define un Codigo MP de referencia: {$codigoMp}. Solo se sugeriran ingresos del mismo material y con diametro igual o mayor al requerido.";
        }

        if ($materiaPrima && $diametroMp) {
            return "El producto ya define materia prima y diametro de referencia: {$materiaPrima} / {$diametroMp}. Solo se sugeriran ingresos del mismo material y con diametro igual o mayor.";
        }

        return 'El producto no tiene MP fija en la tabla de productos. Puedes definirla manualmente en esta etapa.';
    }

    protected function normalizeText(?string $value): ?string
    {
        $value = is_string($value) ? trim($value) : null;
        return $value === '' ? null : $value;
    }

    protected function extractDiameter(?string $value): ?float
    {
        if ($value === null) {
            return null;
        }

        if (preg_match('/[??]?\s*([0-9]+(?:[\.,][0-9]+)?)/u', $value, $matches)) {
            return (float) str_replace(',', '.', $matches[1]);
        }

        return null;
    }

    protected function asFloat($value): float
    {
        if ($value === null || $value === '') {
            return 0.0;
        }

        return (float) str_replace(',', '.', (string) $value);
    }

    protected function roundValue($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return round((float) $value, 2);
    }
}

