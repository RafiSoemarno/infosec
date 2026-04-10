<?php

namespace App\Services;

use App\Models\Equipment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class EquipmentService
{
    /**
     * Return a paginated, filtered list of equipment records.
     *
     * Supported filter keys (all optional, matched with LIKE):
     *   computer_name, serial_number, user, company, plant
     */
    public function search(array $filters = [], int $limit = 25, int $page = 1): LengthAwarePaginator
    {
        $query = Equipment::query();

        $this->applyFilters($query, $filters);

        return $query->paginate($limit, ['*'], 'page', $page);
    }

    private function applyFilters(Builder $query, array $filters): void
    {
        $columns = [
            'computer_name'  => 'computer_name',
            'serial_number'  => 'serial_number',
            'user'           => 'user',
            'company'        => 'company',
            'plant'          => 'plant',
        ];

        foreach ($columns as $param => $column) {
            if (!empty($filters[$param])) {
                $query->where($column, 'like', '%' . $this->escapeLike($filters[$param]) . '%');
            }
        }
    }

    /**
     * Escape LIKE special characters so user input is treated as a literal string.
     */
    private function escapeLike(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }
}
