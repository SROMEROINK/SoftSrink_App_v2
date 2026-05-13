<?php

namespace App\Support;

use Illuminate\Http\Request;

class ModuleTabVisual
{
    /**
     * Resolve the tab visual based on the current request.
     *
     * @return array{module:string,label:string,color:string}
     */
    public static function forRequest(Request $request): array
    {
        $routeName = (string) optional($request->route())->getName();
        $path = trim($request->path(), '/');
        $haystack = strtolower($routeName.' '.$path);

        return self::forModule(self::resolveModule($haystack));
    }

    /**
     * @return array{module:string,label:string,color:string}
     */
    public static function forModule(string $module): array
    {
        $normalizedModule = array_key_exists($module, self::definitions()) ? $module : 'default';
        $definition = self::definitions()[$normalizedModule];

        return [
            'module' => $normalizedModule,
            'label' => $definition['label'],
            'color' => $definition['color'],
        ];
    }

    /**
     * @return array<string, array{label:string,color:string}>
     */
    protected static function definitions(): array
    {
        return [
            'materia_prima' => ['label' => 'MP', 'color' => '#0ea5a4'],
            'pedido_cliente' => ['label' => 'PC', 'color' => '#f59e0b'],
            'entregas' => ['label' => 'EP', 'color' => '#22c55e'],
            'fabricacion' => ['label' => 'FB', 'color' => '#ef4444'],
            'analisis' => ['label' => 'AN', 'color' => '#8b5cf6'],
            'productos' => ['label' => 'PR', 'color' => '#3b82f6'],
            'default' => ['label' => 'SS', 'color' => '#6b7280'],
        ];
    }

    protected static function resolveModule(string $haystack): string
    {
        $analysisNeedles = [
            'resumen',
            'planner',
            'plain',
            'lifecycle',
            'withfiltro',
            'filters',
            'data',
        ];

        foreach ($analysisNeedles as $needle) {
            if (str_contains($haystack, $needle)) {
                return 'analisis';
            }
        }

        if (self::containsAny($haystack, [
            'materia_prima',
            'mp_',
            'mp-',
            'mp/',
        ])) {
            return 'materia_prima';
        }

        if (str_contains($haystack, 'entregas_productos')) {
            return 'entregas';
        }

        if (self::containsAny($haystack, [
            'pedido_cliente',
            'pedido-cliente',
        ])) {
            return 'pedido_cliente';
        }

        if (self::containsAny($haystack, [
            'fabricacion',
            'fechas_of',
        ])) {
            return 'fabricacion';
        }

        if (str_contains($haystack, 'listado_of')) {
            return 'analisis';
        }

        if (self::containsAny($haystack, [
            'producto',
            'productos',
        ])) {
            return 'productos';
        }

        return 'default';
    }

    /**
     * @param  string[]  $needles
     */
    protected static function containsAny(string $haystack, array $needles): bool
    {
        foreach ($needles as $needle) {
            if (str_contains($haystack, $needle)) {
                return true;
            }
        }

        return false;
    }

    public static function buildSvgMarkup(string $label, string $color): string
    {
        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64">
  <rect width="64" height="64" rx="16" fill="{$color}"/>
  <text x="32" y="39" text-anchor="middle" font-family="Arial, sans-serif" font-size="24" font-weight="700" fill="#ffffff">{$label}</text>
</svg>
SVG;
    }
}
