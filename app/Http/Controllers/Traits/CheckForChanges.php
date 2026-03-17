<?php

namespace App\Http\Controllers\Traits;
//app\Http\Controllers\Traits\CheckForChanges.php
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Trait CheckForChanges
 * 
 * Proporciona un método reutilizable para verificar si hay cambios en un modelo
 * antes de guardarlo. Útil para evitar actualizaciones innecesarias en la base de datos.
 * 
 * Uso:
 * 
 * use App\Http\Controllers\Traits\CheckForChanges;
 * 
 * class MiController extends Controller
 * {
 *     use CheckForChanges;
 *     
 *     public function update(Request $request, $id)
 *     {
 *         $model = Modelo::findOrFail($id);
 *         $validatedData = $request->validate([...]);
 *         
 *         return $this->updateIfChanged($model, $validatedData, [
 *             'success_redirect' => route('modelo.index'),
 *             'success_message' => 'Modelo actualizado correctamente.',
 *             'no_changes_message' => 'No se realizaron cambios.',
 *             'set_updated_by' => true, // Opcional: establecer updated_by automáticamente
 *         ]);
 *     }
 * }
 */
trait CheckForChanges
{
    /**
     * Actualiza el modelo solo si hay cambios detectados
     * 
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param array $validatedData
     * @param array $options
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function updateIfChanged($model, array $validatedData, array $options = [])
    {
        // Opciones por defecto
        $defaults = [
            'success_redirect' => null,
            'success_message' => 'Registro actualizado correctamente.',
            'no_changes_message' => 'No se realizaron cambios.',
            'set_updated_by' => true,
            'use_transaction' => false,
            'normalize_data' => false, // Normalizar datos antes de comparar (trim, etc)
        ];

        $options = array_merge($defaults, $options);

        // Normalizar datos si se solicita
        if ($options['normalize_data']) {
            $validatedData = $this->normalizeData($validatedData);
        }

        // Usar transacción si se solicita
        if ($options['use_transaction']) {
            DB::beginTransaction();
        }

        try {
            // Llenar el modelo con los datos validados
            $model->fill($validatedData);

            // Verificar si hay cambios usando isDirty()
            if ($model->isDirty()) {
                // Establecer updated_by si se solicita y el campo existe
                if ($options['set_updated_by'] && $model->getConnection()->getSchemaBuilder()->hasColumn($model->getTable(), 'updated_by')) {
                    $model->updated_by = Auth::id();
                }

                // Guardar el modelo
                $model->save();

                if ($options['use_transaction']) {
                    DB::commit();
                }

                // Respuesta para AJAX
                if (request()->ajax() || request()->wantsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => $options['success_message'],
                        'redirect' => $options['success_redirect']
                    ]);
                }

                // Respuesta para redirección normal
                $redirect = $options['success_redirect'] 
                    ? redirect($options['success_redirect'])
                    : back();

                return $redirect->with('success', $options['success_message']);
            } else {
                // No hay cambios
                if ($options['use_transaction']) {
                    DB::rollBack();
                }

                // Respuesta para AJAX
                if (request()->ajax() || request()->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'type' => 'no_changes',
                        'message' => $options['no_changes_message'],
                        'warning' => $options['no_changes_message'] // Mantener compatibilidad
                    ], 200);
                }

                // Respuesta para redirección normal
                return back()->with('warning', $options['no_changes_message']);
            }
        } catch (\Exception $e) {
            if ($options['use_transaction']) {
                DB::rollBack();
            }

            Log::error('Error al actualizar registro: ' . $e->getMessage(), [
                'model' => get_class($model),
                'id' => $model->getKey(),
                'exception' => $e
            ]);

            // Respuesta para AJAX
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al actualizar el registro: ' . $e->getMessage()
                ], 400);
            }

            // Respuesta para redirección normal
            return back()->with('error', 'Error al actualizar el registro: ' . $e->getMessage());
        }
    }

    /**
     * Normaliza los datos antes de comparar (trim, conversión de tipos, etc)
     * 
     * @param array $data
     * @return array
     */
    protected function normalizeData(array $data)
    {
        $normalized = [];

        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $normalized[$key] = trim($value);
            } elseif (is_numeric($value)) {
                // Mantener números como están, pero asegurar formato consistente
                $normalized[$key] = is_float($value) 
                    ? number_format((float)$value, 2, '.', '') 
                    : (int)$value;
            } else {
                $normalized[$key] = $value;
            }
        }

        return $normalized;
    }
}

