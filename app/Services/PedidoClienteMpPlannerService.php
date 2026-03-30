<?php

namespace App\Services;

use App\Models\MpIngreso;
use App\Models\MpMovimientoAdicional;
use App\Models\MpSalidaInicial;
use App\Models\PedidoCliente;
use App\Models\PedidoClienteMp;
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

        $ingresos = $this->getCompatibleIngresos(
            $codigoMp,
            $materiaPrima,
            $diametroMp,
            isset($input['current_pedido_mp_id']) ? (int) $input['current_pedido_mp_id'] : null
        );
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
                'mensaje' => $this->buildCompatibilityMessage($productoCodigoMp, $productoMaterial, $productoDiametro, $diametroMp),
            ],
            'ingresos' => $ingresos->values()->all(),
            'stock' => $stockSummary,
        ];
    }

    public function buildStockDashboard(?int $excludePedidoMpId = null): array
    {
        $rows = $this->buildNetStockRows($excludePedidoMpId)->values();

        return [
            'rows' => $rows->all(),
            'summary' => [
                'ingresos_con_stock' => $rows->count(),
                'total_barras_disponibles' => (int) $rows->sum('Barras_Disponibles'),
                'total_metros_disponibles' => $this->roundValue($rows->sum('Mts_Disponibles')) ?? 0.0,
                'total_barras_reservadas' => (int) $rows->sum('Barras_Reservadas'),
            ],
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

    protected function getCompatibleIngresos(?string $codigoMp, ?string $materiaPrima, ?string $diametroMp, ?int $excludePedidoMpId = null): Collection
    {
        $diametroRequerido = $this->extractDiameter($diametroMp ?? $codigoMp);
        $diametroMaximoPermitido = $this->resolveMaxAllowedDiameter($diametroMp ?? $codigoMp);
        $materiaRequerida = $this->normalizeText($materiaPrima);

        return $this->buildNetStockRows($excludePedidoMpId)
            ->when($materiaRequerida, function (Collection $rows) use ($materiaRequerida) {
                return $rows->filter(fn ($row) => $this->normalizeText($row['Materia_Prima'] ?? null) === $materiaRequerida);
            })
            ->filter(function ($ingreso) use ($diametroRequerido, $diametroMaximoPermitido, $materiaPrima) {
                $diametroIngreso = $this->extractDiameter($ingreso['Diametro_MP'] ?? $ingreso['Codigo_MP'] ?? null);
                $materiaIngreso = $this->normalizeText($ingreso['Materia_Prima'] ?? null);
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

                if ($diametroIngreso < $diametroRequerido) {
                    return false;
                }

                if ($diametroMaximoPermitido !== null && $diametroIngreso > $diametroMaximoPermitido) {
                    return false;
                }

                return true;
            })
            ->values();
    }

    protected function buildNetStockRows(?int $excludePedidoMpId = null): Collection
    {
        $ingresos = MpIngreso::query()
            ->with(['proveedor', 'materiaPrima', 'diametro'])
            ->whereNull('deleted_at')
            ->where('reg_Status', 1)
            ->orderBy('Nro_Ingreso_MP')
            ->get();

        $salidasIniciales = MpSalidaInicial::query()
            ->whereNull('deleted_at')
            ->get()
            ->keyBy('Id_Ingreso_MP');

        $movimientos = MpMovimientoAdicional::query()
            ->whereNull('deleted_at')
            ->selectRaw('Nro_Ingreso_MP, SUM(Cantidad_Adicionales) as adicionales, SUM(Cantidad_Devoluciones) as devoluciones')
            ->groupBy('Nro_Ingreso_MP')
            ->get()
            ->keyBy('Nro_Ingreso_MP');

        $reservas = PedidoClienteMp::query()
            ->whereNull('deleted_at')
            ->whereNotNull('Nro_Ingreso_MP')
            ->when($excludePedidoMpId, fn ($query) => $query->where('Id_Pedido_MP', '!=', $excludePedidoMpId))
            ->selectRaw('Nro_Ingreso_MP, SUM(Cant_Barras_MP) as reservadas')
            ->groupBy('Nro_Ingreso_MP')
            ->get()
            ->keyBy('Nro_Ingreso_MP');

        $egresosPedidos = \DB::table('mp_salidas as s')
            ->join('pedido_cliente_mp as pm', 'pm.Id_OF', '=', 's.Id_OF_Salidas_MP')
            ->whereNull('s.deleted_at')
            ->whereNull('pm.deleted_at')
            ->selectRaw('pm.Nro_Ingreso_MP, SUM(s.Total_Salidas_MP) as egresadas, SUM(s.Total_Mtros_Utilizados) as metros_egresados')
            ->groupBy('pm.Nro_Ingreso_MP')
            ->get()
            ->keyBy('Nro_Ingreso_MP');

        return $ingresos
            ->map(function (MpIngreso $ingreso) use ($salidasIniciales, $movimientos, $reservas, $egresosPedidos) {
                $salidaInicial = $salidasIniciales->get($ingreso->Id_MP);
                $movimiento = $movimientos->get($ingreso->Nro_Ingreso_MP);
                $reserva = $reservas->get($ingreso->Nro_Ingreso_MP);
                $egresoPedido = $egresosPedidos->get($ingreso->Nro_Ingreso_MP);

                $barrasIngreso = (int) ($ingreso->Unidades_MP ?? 0);
                $devolucionesProveedor = (int) ($salidaInicial->Devoluciones_Proveedor ?? 0);
                $stockInicial = (int) ($salidaInicial->Stock_Inicial ?? $barrasIngreso);
                $ajusteStock = (int) ($salidaInicial->Ajuste_Stock ?? 0);
                $salidasFinalBase = (int) ($stockInicial - $devolucionesProveedor + $ajusteStock);
                $barrasBaseTeoricas = $barrasIngreso - $salidasFinalBase;
                $barrasBase = max(0, $barrasBaseTeoricas);
                $longitudUnidad = $this->asFloat($ingreso->Longitud_Unidad_MP ?? 0);
                $devoluciones = (int) ($movimiento->devoluciones ?? 0);
                $adicionales = (int) ($movimiento->adicionales ?? 0);
                $reservadas = (int) ($reserva->reservadas ?? 0);
                $egresadasPedidos = (int) ($egresoPedido->egresadas ?? 0);
                $metrosEgresadosPedidos = $this->asFloat($egresoPedido->metros_egresados ?? 0);
                $reservadasPendientes = max(0, $reservadas - $egresadasPedidos);
                $saldoTeorico = $barrasBaseTeoricas + $devoluciones - $adicionales - $egresadasPedidos - $reservadasPendientes;
                $barrasDisponibles = max(0, $saldoTeorico);
                $mtsDisponibles = $saldoTeorico * $longitudUnidad;
                $tieneMasSalidasQueIngresos = $salidasFinalBase > $barrasIngreso;
                $tieneIngresosExtra = $salidasFinalBase < 0;
                $tieneStockNegativo = $saldoTeorico < 0;
                $tieneAlertaStock = $tieneMasSalidasQueIngresos || $tieneIngresosExtra || $tieneStockNegativo;

                $motivosAlerta = [];
                $tipoAlerta = '';
                $botonAlertaTexto = '';
                $botonAlertaClase = 'btn-secondary';

                if ($tieneMasSalidasQueIngresos) {
                    $motivosAlerta[] = 'Mas salidas que ingresos';
                    $tipoAlerta = 'mas_salidas';
                    $botonAlertaTexto = 'Mas salidas que ingresos';
                    $botonAlertaClase = 'btn-danger';
                }

                if ($tieneIngresosExtra) {
                    $motivosAlerta[] = 'Ingresos extra vs original';
                    if ($tipoAlerta === '') {
                        $tipoAlerta = 'ingresos_extra';
                        $botonAlertaTexto = 'Ingresos extra al original';
                        $botonAlertaClase = 'btn-warning';
                    }
                }

                if ($tieneStockNegativo) {
                    $motivosAlerta[] = 'Stock negativo';
                    if ($tipoAlerta === '') {
                        $tipoAlerta = 'stock_negativo';
                        $botonAlertaTexto = 'Stock negativo';
                        $botonAlertaClase = 'btn-danger';
                    }
                }

                $motivoAlerta = empty($motivosAlerta) ? '' : implode(' | ', array_unique($motivosAlerta));
                $cantidadAlertaBarras = $saldoTeorico < 0 ? abs($saldoTeorico) : abs(min(0, $barrasBaseTeoricas));
                $cantidadAlertaMetros = abs($mtsDisponibles);

                return [
                    'Id_MP' => (int) $ingreso->Id_MP,
                    'Nro_Ingreso_MP' => (int) $ingreso->Nro_Ingreso_MP,
                    'Fecha_Ingreso' => $ingreso->Fecha_Ingreso
                        ? $ingreso->Fecha_Ingreso->format('Y-m-d')
                        : '',
                    'Proveedor' => $ingreso->proveedor->Prov_Nombre ?? null,
                    'Pedido_Proveedor_MP' => blank($ingreso->Nro_Pedido) ? null : $ingreso->Nro_Pedido,
                    'Nro_Certificado_MP' => $ingreso->Nro_Certificado_MP,
                    'Materia_Prima' => $ingreso->materiaPrima->Nombre_Materia ?? null,
                    'Diametro_MP' => $ingreso->diametro->Valor_Diametro ?? null,
                    'Codigo_MP' => $ingreso->Codigo_MP,
                    'Barras_Ingreso' => $barrasIngreso,
                    'Stock_Inicial' => $stockInicial,
                    'Devoluciones_Proveedor' => $devolucionesProveedor,
                    'Ajuste_Stock' => $ajusteStock,
                    'Barras_Base' => max(0, $barrasBase),
                    'Salidas_Final_Base' => max(0, $salidasFinalBase),
                    'Cantidad_Devoluciones_Stock' => $devoluciones,
                    'Cantidad_Adicionales_Stock' => $adicionales,
                    'Barras_Reservadas' => $reservadas,
                    'Barras_Egresadas_Pedidos' => $egresadasPedidos,
                    'Metros_Egresados_Pedidos' => $this->roundValue($metrosEgresadosPedidos) ?? 0.0,
                    'Barras_Reservadas_Pendientes' => $reservadasPendientes,
                    'Saldo_Teorico' => $saldoTeorico,
                    'Tiene_Inconsistencia_Base' => $tieneMasSalidasQueIngresos,
                    'Tiene_Ingresos_Extra' => $tieneIngresosExtra,
                    'Tiene_Stock_Negativo' => $tieneStockNegativo,
                    'Tiene_Alerta_Stock' => $tieneAlertaStock,
                    'Tipo_Alerta' => $tipoAlerta,
                    'Motivo_Alerta' => $motivoAlerta,
                    'Boton_Alerta_Texto' => $botonAlertaTexto,
                    'Boton_Alerta_Clase' => $botonAlertaClase,
                    'Cantidad_Alerta_Barras' => $cantidadAlertaBarras,
                    'Cantidad_Alerta_Metros' => $this->roundValue($cantidadAlertaMetros) ?? 0.0,
                    'Barras_Disponibles' => $barrasDisponibles,
                    'Longitud_Unidad_MP' => $this->roundValue($longitudUnidad) ?? 0.0,
                    'Mts_Disponibles' => $this->roundValue($mtsDisponibles) ?? 0.0,
                    'Detalle_Origen_MP' => $ingreso->Detalle_Origen_MP,
                    'Nro_Pedido_Proveedor' => blank($ingreso->Nro_Pedido) ? null : $ingreso->Nro_Pedido,
                    'Tiene_Salida_Inicial' => (bool) $salidaInicial,
                    'Url_Salida_Inicial' => $salidaInicial
                        ? route('mp_salidas_iniciales.editMassive', ['id' => $ingreso->Id_MP, 'return_to' => 'stock_mp'])
                        : route('mp_salidas_iniciales.create', ['ingreso_mp' => $ingreso->Id_MP, 'return_to' => 'stock_mp']),
                    'Texto_Salida_Inicial' => $salidaInicial ? 'Editar ajuste' : 'Registrar ajuste',
                    'Unidades_MP' => max(0, $barrasDisponibles),
                    'Mts_Totales' => $this->roundValue($mtsDisponibles) ?? 0.0,
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

    protected function buildCompatibilityMessage(?string $codigoMp, ?string $materiaPrima, ?string $productoDiametroMp, ?string $diametroSeleccionado = null): string
    {
        $diametroBase = $diametroSeleccionado ?: $productoDiametroMp ?: $codigoMp;
        $diametroMaximoPermitido = $this->resolveMaxAllowedDiameterLabel($diametroBase);

        $rangoMensaje = $diametroMaximoPermitido && $diametroBase
            ? " y con diametro desde {$diametroBase} hasta {$diametroMaximoPermitido}"
            : ' y con diametro igual o mayor al requerido';

        if ($codigoMp) {
            return "El producto ya define un Codigo MP de referencia: {$codigoMp}. Solo se sugeriran ingresos del mismo material{$rangoMensaje}.";
        }

        if ($materiaPrima && $productoDiametroMp) {
            return "El producto ya define materia prima y diametro de referencia: {$materiaPrima} / {$productoDiametroMp}. Solo se sugeriran ingresos del mismo material{$rangoMensaje}.";
        }

        return 'El producto no tiene MP fija en la tabla de productos. Puedes definirla manualmente en esta etapa.';
    }

    protected function resolveMaxAllowedDiameter(?string $diametroBase): ?float
    {
        $item = $this->resolveAllowedDiameterItem($diametroBase);

        return $item['value'] ?? null;
    }

    protected function resolveMaxAllowedDiameterLabel(?string $diametroBase): ?string
    {
        $item = $this->resolveAllowedDiameterItem($diametroBase);

        return $item['label'] ?? null;
    }

    protected function resolveAllowedDiameterItem(?string $diametroBase): ?array
    {
        $diametroBaseValue = $this->extractDiameter($diametroBase);
        if ($diametroBaseValue === null) {
            return null;
        }

        $diametros = DB::table('mp_diametro')
            ->where('reg_Status', 1)
            ->whereNull('deleted_at')
            ->orderBy('Id_Diametro')
            ->pluck('Valor_Diametro')
            ->map(function ($valor) {
                return [
                    'label' => $valor,
                    'value' => $this->extractDiameter($valor),
                ];
            })
            ->filter(fn ($item) => $item['value'] !== null)
            ->values();

        if ($diametros->isEmpty()) {
            return null;
        }

        $startIndex = $diametros->search(function ($item) use ($diametroBaseValue) {
            return abs($item['value'] - $diametroBaseValue) < 0.0001;
        });

        if ($startIndex === false) {
            $startIndex = $diametros->search(function ($item) use ($diametroBaseValue) {
                return $item['value'] >= $diametroBaseValue;
            });
        }

        if ($startIndex === false) {
            return null;
        }

        $maxIndex = min($startIndex + 3, $diametros->count() - 1);

        return $diametros->get($maxIndex);
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

        if (preg_match('/[ÃƒÆ’Ã†â€™Ãƒâ€¹Ã…â€œÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬â„¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬]?\s*([0-9]+(?:[\.,][0-9]+)?)/u', $value, $matches)) {
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

