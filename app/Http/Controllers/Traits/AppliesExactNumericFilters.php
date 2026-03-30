<?php

namespace App\Http\Controllers\Traits;

use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

trait AppliesExactNumericFilters
{
    protected function applySmartFilter(QueryBuilder|EloquentBuilder $query, string $column, mixed $rawValue): void
    {
        $value = trim((string) $rawValue);

        if ($value == '') {
            return;
        }

        if ($this->isExactNumericFilter($value)) {
            $query->where($column, $this->castNumericFilterValue($value));
            return;
        }

        $query->where($column, 'like', '%' . $value . '%');
    }

    protected function isExactNumericFilter(string $value): bool
    {
        return preg_match('/^-?\d+(?:[.,]\d+)?$/', $value) === 1;
    }

    protected function castNumericFilterValue(string $value): int|float
    {
        $normalized = str_replace(',', '.', $value);

        return str_contains($normalized, '.')
            ? (float) $normalized
            : (int) $normalized;
    }
}
