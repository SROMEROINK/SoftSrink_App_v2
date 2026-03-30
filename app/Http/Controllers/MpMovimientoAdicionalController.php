<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\AppliesExactNumericFilters;
use App\Models\MpIngreso;
use App\Models\MpMovimientoAdicional;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class MpMovimientoAdicionalController extends Controller
{
    use AppliesExactNumericFilters;
    protected string $legacyCsvPath = 'C:\\Users\\SergioDanielRomero\\Documents\\SQL_SRINK_LARAVEL_11\\CARGA EXCEL-DB\\MP\\movimientos_adicionales_mp.csv';

    public function index()
    {
        $totalMovimientos = MpMovimientoAdicional::count();
        $movimientosActivos = MpMovimientoAdicional::where('reg_Status', 1)->count();
        $netoMetros = (float) MpMovimientoAdicional::sum('Total_Mtros_Movimiento');
        $csvDisponible = is_file($this->legacyCsvPath);
        $puedeImportarHistorico = $csvDisponible && $totalMovimientos === 0;

        return view('materia_prima.movimientos_adicionales.index', compact(
            'totalMovimientos',
            'movimientosActivos',
            'netoMetros',
            'csvDisponible',
            'puedeImportarHistorico'
        ));
    }

    public function getData(Request $request)
    {
        $query = MpMovimientoAdicional::query()->whereNull('deleted_at');

        if ($request->filled('filtro_ingreso')) {
            $this->applySmartFilter($query, 'Nro_Ingreso_MP', $request->filtro_ingreso);
        }

        if ($request->filled('filtro_of')) {
            $this->applySmartFilter($query, 'Nro_OF', $request->filtro_of);
        }

        if ($request->filled('filtro_producto')) {
            $query->where('Codigo_Producto', 'like', '%' . $request->filtro_producto . '%');
        }

        if ($request->filled('filtro_maquina')) {
            $query->where('Nro_Maquina', 'like', '%' . $request->filtro_maquina . '%');
        }

        if ($request->filled('filtro_tipo')) {
            if ($request->filtro_tipo === 'ADICIONAL') {
                $query->where('Cantidad_Adicionales', '>', 0);
            } elseif ($request->filtro_tipo === 'DEVOLUCION') {
                $query->where('Cantidad_Devoluciones', '>', 0);
            }
        }

        return datatables()->of($query)
            ->editColumn('Fecha_Movimiento', fn ($row) => $row->Fecha_Movimiento ? optional($row->Fecha_Movimiento)->format('d/m/Y') : '')
            ->editColumn('Cantidad_Adicionales', fn ($row) => number_format((int) $row->Cantidad_Adicionales, 0, ',', '.'))
            ->editColumn('Cantidad_Devoluciones', fn ($row) => number_format((int) $row->Cantidad_Devoluciones, 0, ',', '.'))
            ->editColumn('Longitud_Unidad_Mts', fn ($row) => $row->Longitud_Unidad_Mts !== null ? number_format((float) $row->Longitud_Unidad_Mts, 2, ',', '.') : '')
            ->editColumn('Total_Mtros_Movimiento', fn ($row) => $row->Total_Mtros_Movimiento !== null ? number_format((float) $row->Total_Mtros_Movimiento, 2, ',', '.') : '')
            ->addColumn('Tipo_Movimiento', function ($row) {
                if ((int) $row->Cantidad_Adicionales > 0 && (int) $row->Cantidad_Devoluciones === 0) {
                    return 'Adicional';
                }
                if ((int) $row->Cantidad_Devoluciones > 0 && (int) $row->Cantidad_Adicionales === 0) {
                    return 'Devolucion';
                }

                return 'Mixto';
            })
            ->addColumn('acciones', function ($row) {
                return '
                    <div class="acciones-grupo">
                        <a href="' . route('mp_movimientos_adicionales.show', $row->Id_Movimiento_MP) . '" class="btn btn-info btn-sm">Ver</a>
                        <a href="' . route('mp_movimientos_adicionales.edit', $row->Id_Movimiento_MP) . '" class="btn btn-primary btn-sm">Editar</a>
                        <button type="button" class="btn btn-danger btn-sm btn-delete-mov-adicional" data-id="' . $row->Id_Movimiento_MP . '">Eliminar</button>
                    </div>
                ';
            })
            ->rawColumns(['acciones'])
            ->make(true);
    }

    public function create()
    {
        $ingresos = MpIngreso::query()->whereNull('deleted_at')->orderBy('Nro_Ingreso_MP')->get(['Id_MP', 'Nro_Ingreso_MP', 'Codigo_MP', 'Nro_Certificado_MP', 'Longitud_Unidad_MP']);
        $users = User::orderBy('name')->get(['id', 'name']);

        return view('materia_prima.movimientos_adicionales.create', compact('ingresos', 'users'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateData($request);

        try {
            $data = $this->buildPayload($validated);
            $data['created_by'] = Auth::id();
            $data['updated_by'] = Auth::id();
            MpMovimientoAdicional::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Movimiento adicional de MP creado correctamente.',
                'redirect' => route('mp_movimientos_adicionales.index'),
            ]);
        } catch (\Throwable $e) {
            Log::error('Error al crear mp_movimientos_adicionales', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'No se pudo crear el movimiento adicional de MP.',
            ], 400);
        }
    }

    public function show($id)
    {
        $movimiento = MpMovimientoAdicional::findOrFail($id);
        return view('materia_prima.movimientos_adicionales.show', compact('movimiento'));
    }

    public function edit($id)
    {
        $movimiento = MpMovimientoAdicional::findOrFail($id);
        $ingresos = MpIngreso::query()->whereNull('deleted_at')->orderBy('Nro_Ingreso_MP')->get(['Id_MP', 'Nro_Ingreso_MP', 'Codigo_MP', 'Nro_Certificado_MP', 'Longitud_Unidad_MP']);
        $users = User::orderBy('name')->get(['id', 'name']);

        return view('materia_prima.movimientos_adicionales.edit', compact('movimiento', 'ingresos', 'users'));
    }

    public function update(Request $request, $id)
    {
        $movimiento = MpMovimientoAdicional::findOrFail($id);
        $validated = $this->validateData($request);
        $data = $this->buildPayload($validated);

        if (!$this->hasMeaningfulChanges($movimiento, $data)) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'type' => 'no_changes',
                    'message' => 'No se detectaron cambios en el formulario.',
                ]);
            }

            return redirect()->back()->with('warning', 'No se realizaron cambios.');
        }

        $movimiento->fill($data);
        $movimiento->updated_by = Auth::id();
        $movimiento->save();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Movimiento adicional actualizado correctamente.',
                'redirect' => route('mp_movimientos_adicionales.index'),
            ]);
        }

        return redirect()->route('mp_movimientos_adicionales.index')->with('success', 'Movimiento adicional actualizado correctamente.');
    }

    public function destroy($id)
    {
        try {
            $movimiento = MpMovimientoAdicional::findOrFail($id);
            $movimiento->deleted_by = Auth::id();
            $movimiento->save();
            $movimiento->delete();

            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            Log::error('Error al eliminar mp_movimientos_adicionales', ['error' => $e->getMessage()]);
            return response()->json(['success' => false], 400);
        }
    }

    public function showDeleted()
    {
        $movimientosEliminados = MpMovimientoAdicional::onlyTrashed()->orderByDesc('deleted_at')->get();
        return view('materia_prima.movimientos_adicionales.deleted', compact('movimientosEliminados'));
    }

    public function restore($id)
    {
        try {
            $movimiento = MpMovimientoAdicional::withTrashed()->findOrFail($id);
            $movimiento->restore();
            return redirect()->route('mp_movimientos_adicionales.index')->with('success', 'Movimiento adicional restaurado correctamente.');
        } catch (\Throwable $e) {
            Log::error('Error al restaurar mp_movimientos_adicionales', ['error' => $e->getMessage()]);
            return redirect()->route('mp_movimientos_adicionales.deleted')->with('error', 'No se pudo restaurar el movimiento adicional.');
        }
    }

    public function importLegacyCsv(Request $request)
    {
        try {
            if (MpMovimientoAdicional::count() > 0) {
                return redirect()
                    ->route('mp_movimientos_adicionales.index')
                    ->with('warning', 'La importacion historica solo puede ejecutarse una vez. Ya existen movimientos cargados.');
            }

            $imported = $this->importCsvFile($this->legacyCsvPath, true);

            return redirect()->route('mp_movimientos_adicionales.index')->with(
                $imported > 0 ? 'success' : 'warning',
                $imported > 0
                    ? 'Se importaron ' . number_format($imported, 0, ',', '.') . ' movimientos historicos desde el CSV.'
                    : 'El CSV ya estaba importado o no se encontraron filas nuevas.'
            );
        } catch (\Throwable $e) {
            Log::error('Error al importar CSV de movimientos adicionales', ['error' => $e->getMessage()]);
            return redirect()->route('mp_movimientos_adicionales.index')->with('error', 'No se pudo importar el CSV historico.');
        }
    }

    protected function validateData(Request $request): array
    {
        $validated = $request->validate([
            'Fecha_Movimiento' => 'required|date',
            'Nro_Ingreso_MP' => 'required|integer|min:1|exists:mp_ingreso,Nro_Ingreso_MP',
            'Nro_OF' => 'nullable|integer|min:0',
            'Codigo_Producto' => 'nullable|string|max:255',
            'Nro_Maquina' => 'nullable|string|max:50',
            'Cantidad_Adicionales' => 'nullable|integer|min:0',
            'Cantidad_Devoluciones' => 'nullable|integer|min:0',
            'Longitud_Unidad_Mts' => 'nullable|numeric|min:0',
            'Autorizado_por' => 'nullable|integer|exists:users,id',
            'Observaciones' => 'nullable|string',
            'reg_Status' => 'required|in:0,1',
        ]);

        $adicionales = (int) ($validated['Cantidad_Adicionales'] ?? 0);
        $devoluciones = (int) ($validated['Cantidad_Devoluciones'] ?? 0);

        if ($adicionales === 0 && $devoluciones === 0) {
            throw ValidationException::withMessages([
                'Cantidad_Adicionales' => 'Debes ingresar al menos una cantidad en Adicionales o Devoluciones.',
                'Cantidad_Devoluciones' => 'Debes ingresar al menos una cantidad en Adicionales o Devoluciones.',
            ]);
        }

        return $validated;
    }

    protected function buildPayload(array $validated): array
    {
        $ingreso = MpIngreso::query()
            ->whereNull('deleted_at')
            ->where('Nro_Ingreso_MP', $validated['Nro_Ingreso_MP'])
            ->first();

        $fecha = $validated['Fecha_Movimiento'];
        $adicionales = (int) ($validated['Cantidad_Adicionales'] ?? 0);
        $devoluciones = (int) ($validated['Cantidad_Devoluciones'] ?? 0);
        $longitud = (float) ($validated['Longitud_Unidad_Mts'] ?? ($ingreso->Longitud_Unidad_MP ?? 0));
        $total = round(($adicionales - $devoluciones) * $longitud, 2);

        return [
            'Fecha_Movimiento' => $fecha,
            'Mes' => strftime('%B', strtotime($fecha)),
            'Anio' => (int) date('Y', strtotime($fecha)),
            'Nro_Ingreso_MP' => (int) $validated['Nro_Ingreso_MP'],
            'Concatenar_Proveedor' => $ingreso ? ($ingreso->proveedor->Prov_Nombre ?? null) . '_' . ($ingreso->Codigo_MP ?? '') : null,
            'Materia_Prima' => $ingreso?->materiaPrima?->Nombre_Materia,
            'Diametro_MP' => $ingreso?->diametro?->Valor_Diametro,
            'Nro_Certificado_MP' => $ingreso->Nro_Certificado_MP ?? null,
            'Nro_OF' => $validated['Nro_OF'] ?? null,
            'Codigo_Producto' => $validated['Codigo_Producto'] ?? $this->resolveCodigoProducto($validated['Nro_OF'] ?? null),
            'Nro_Maquina' => $validated['Nro_Maquina'] ?? null,
            'Cantidad_Adicionales' => $adicionales,
            'Cantidad_Devoluciones' => $devoluciones,
            'Longitud_Unidad_Mts' => $longitud,
            'Total_Mtros_Movimiento' => $total,
            'Autorizado_por' => $validated['Autorizado_por'] ?? null,
            'Observaciones' => $validated['Observaciones'] ?? null,
            'reg_Status' => (int) $validated['reg_Status'],
        ];
    }

    protected function hasMeaningfulChanges(MpMovimientoAdicional $movimiento, array $data): bool
    {
        $keys = [
            'Fecha_Movimiento',
            'Nro_Ingreso_MP',
            'Nro_OF',
            'Codigo_Producto',
            'Nro_Maquina',
            'Cantidad_Adicionales',
            'Cantidad_Devoluciones',
            'Longitud_Unidad_Mts',
            'Autorizado_por',
            'Observaciones',
            'reg_Status',
        ];

        foreach ($keys as $key) {
            $current = $this->normalizeComparableValue($movimiento->{$key} ?? null);
            $incoming = $this->normalizeComparableValue($data[$key] ?? null);

            if ($current !== $incoming) {
                return true;
            }
        }

        return false;
    }

    protected function normalizeComparableValue($value): string
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        if ($value === null) {
            return '';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_numeric($value)) {
            return number_format((float) $value, 4, '.', '');
        }

        return trim((string) $value);
    }

    protected function importCsvFile(string $path, bool $skipExisting = true): int
    {
        if (!is_file($path)) {
            throw new \RuntimeException('No se encontro el CSV historico en la ruta esperada.');
        }

        $handle = fopen($path, 'r');
        if (!$handle) {
            throw new \RuntimeException('No se pudo abrir el CSV historico.');
        }

        $headers = null;
        $imported = 0;

        DB::beginTransaction();
        try {
            while (($row = fgetcsv($handle, 0, ';')) !== false) {
                if ($headers === null) {
                    $headers = $this->normalizeHeaders($row);
                    continue;
                }

                if ($this->rowIsEmpty($row)) {
                    continue;
                }

                $mapped = $this->mapCsvRow(array_combine($headers, array_pad($row, count($headers), null)));
                if (!$mapped) {
                    continue;
                }

                if ($skipExisting && $this->existsImportedRow($mapped)) {
                    continue;
                }

                MpMovimientoAdicional::create($mapped + [
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                    'reg_Status' => 1,
                ]);
                $imported++;
            }

            fclose($handle);
            DB::commit();
        } catch (\Throwable $e) {
            fclose($handle);
            DB::rollBack();
            throw $e;
        }

        return $imported;
    }

    protected function mapCsvRow(array $row): ?array
    {
        $fecha = $this->normalizeDate($row['Fecha'] ?? null);
        $nroIngreso = $this->toNullableInt($row['NÂ° Ingreso MP'] ?? $row['Nro Ingreso MP'] ?? null);
        $nroOf = $this->toNullableInt($row['NÂ° OF'] ?? $row['Nro OF'] ?? null);
        $adicionales = $this->toNullableInt($row['Adicionales'] ?? null) ?? 0;
        $devoluciones = $this->toNullableInt($row['Devoluciones'] ?? null) ?? 0;
        $longitud = $this->toNullableDecimal($row['Longitud (Mts)'] ?? $row['Longitud(Mts)'] ?? null) ?? 0.0;

        if (!$fecha && !$nroIngreso && !$nroOf) {
            return null;
        }

        $ingreso = $nroIngreso
            ? MpIngreso::query()->with(['proveedor', 'materiaPrima', 'diametro'])->whereNull('deleted_at')->where('Nro_Ingreso_MP', $nroIngreso)->first()
            : null;

        return [
            'Fecha_Movimiento' => $fecha,
            'Mes' => $row['Mes'] ?? null,
            'Anio' => $this->toNullableInt($row['AÃ±o'] ?? $row['Anio'] ?? null),
            'Nro_Ingreso_MP' => $nroIngreso,
            'Concatenar_Proveedor' => $row['Concatenar_Proveedor'] ?? null,
            'Materia_Prima' => $row['MATERIA PRIMA:'] ?? $ingreso?->materiaPrima?->Nombre_Materia,
            'Diametro_MP' => $row['DIAMETRO:'] ?? $ingreso?->diametro?->Valor_Diametro,
            'Nro_Certificado_MP' => $row['NÂ° de Certificado MP'] ?? $row['Nro de Certificado MP'] ?? $ingreso?->Nro_Certificado_MP,
            'Nro_OF' => $nroOf,
            'Codigo_Producto' => $this->resolveCodigoProducto($nroOf) ?? ($row['CÃ³digo de Producto'] ?? $row['Codigo de Producto'] ?? null),
            'Nro_Maquina' => $row['NÂ° de MÃ¡quina'] ?? $row['Nro de Maquina'] ?? null,
            'Cantidad_Adicionales' => $adicionales,
            'Cantidad_Devoluciones' => $devoluciones,
            'Longitud_Unidad_Mts' => $longitud,
            'Total_Mtros_Movimiento' => round(($adicionales - $devoluciones) * $longitud, 2),
            'Autorizado_por' => null,
            'Observaciones' => 'Importado desde CSV historico de movimientos adicionales MP',
        ];
    }

    protected function existsImportedRow(array $mapped): bool
    {
        return MpMovimientoAdicional::query()
            ->whereDate('Fecha_Movimiento', $mapped['Fecha_Movimiento'])
            ->where('Nro_Ingreso_MP', $mapped['Nro_Ingreso_MP'])
            ->where('Nro_OF', $mapped['Nro_OF'])
            ->where('Nro_Maquina', $mapped['Nro_Maquina'])
            ->where('Cantidad_Adicionales', $mapped['Cantidad_Adicionales'])
            ->where('Cantidad_Devoluciones', $mapped['Cantidad_Devoluciones'])
            ->where('Longitud_Unidad_Mts', $mapped['Longitud_Unidad_Mts'])
            ->exists();
    }

    protected function resolveCodigoProducto(?int $nroOf): ?string
    {
        if (!$nroOf) {
            return null;
        }

        $codigoActual = DB::table('pedido_cliente as pc')
            ->join('productos as p', 'p.Id_Producto', '=', 'pc.Producto_Id')
            ->where('pc.Nro_OF', $nroOf)
            ->whereNull('pc.deleted_at')
            ->value('p.Prod_Codigo');

        if ($codigoActual) {
            return $codigoActual;
        }

        return DB::table('listado_of as lo')
            ->join('productos as p', 'p.Id_Producto', '=', 'lo.Producto_Id')
            ->where('lo.Nro_OF', $nroOf)
            ->value('p.Prod_Codigo');
    }

    protected function normalizeHeaders(array $headers): array
    {
        return array_map(function ($header) {
            $header = preg_replace('/^\xEF\xBB\xBF/', '', (string) $header);
            return trim($header);
        }, $headers);
    }

    protected function rowIsEmpty(array $row): bool
    {
        foreach ($row as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }
        return true;
    }

    protected function normalizeDate(?string $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        foreach (['d/m/Y', 'Y-m-d', 'd-m-Y'] as $format) {
            $date = \DateTime::createFromFormat($format, $value);
            if ($date && $date->format($format) === $value) {
                return $date->format('Y-m-d');
            }
        }

        return null;
    }

    protected function toNullableInt($value): ?int
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }
        $value = preg_replace('/[^0-9\-]/', '', $value);
        return $value === '' ? null : (int) $value;
    }

    protected function toNullableDecimal($value): ?float
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }
        $value = str_replace('.', '', $value);
        $value = str_replace(',', '.', $value);
        return is_numeric($value) ? (float) $value : null;
    }
}
