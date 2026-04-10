<?php

namespace App\Services;

use App\Models\DrillLogMain;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class DrillLogService
{
    /**
     * Return a paginated, filtered list of drill log records.
     *
     * Supported filter keys (all optional):
     *   from          – inclusive start datetime (ISO-8601 / any parseable date string)
     *   to            – inclusive end datetime
     *   computer_name – partial match
     *   user          – partial match
     */
    public function search(array $filters = [], int $limit = 25, int $page = 1): LengthAwarePaginator
    {
        $query = DrillLogMain::query();

        $this->applyFilters($query, $filters);

        return $query->paginate($limit, ['*'], 'page', $page);
    }

    private function applyFilters(Builder $query, array $filters): void
    {
        if (!empty($filters['from'])) {
            $query->where('log_time', '>=', $filters['from']);
        }

        if (!empty($filters['to'])) {
            $query->where('log_time', '<=', $filters['to']);
        }

        if (!empty($filters['computer_name'])) {
            $query->where('computer_name', 'like', '%' . $this->escapeLike($filters['computer_name']) . '%');
        }

        if (!empty($filters['user'])) {
            $query->where('user', 'like', '%' . $this->escapeLike($filters['user']) . '%');
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
