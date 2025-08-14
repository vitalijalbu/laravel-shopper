<?php

namespace LaravelShopper\DataTable;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

abstract class BaseDataTable
{
    protected Builder $query;
    protected Request $request;
    protected array $filters = [];
    protected array $columns = [];
    protected array $searchableColumns = [];
    protected array $sortableColumns = [];
    protected string $defaultSort = 'id';
    protected string $defaultDirection = 'desc';
    protected int $perPage = 25;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->query = $this->query();
        $this->setupFilters();
        $this->setupColumns();
    }

    /**
     * Get the base query for the datatable.
     */
    abstract protected function query(): Builder;

    /**
     * Setup filters for the datatable.
     */
    abstract protected function setupFilters(): void;

    /**
     * Setup columns for the datatable.
     */
    abstract protected function setupColumns(): void;

    /**
     * Apply filters to query.
     */
    public function applyFilters(): static
    {
        foreach ($this->filters as $filter) {
            $filter->apply($this->query, $this->request);
        }

        return $this;
    }

    /**
     * Apply search to query.
     */
    public function applySearch(): static
    {
        $search = $this->request->query('search');

        if ($search && !empty($this->searchableColumns)) {
            $this->query->where(function (Builder $query) use ($search) {
                foreach ($this->searchableColumns as $column) {
                    $query->orWhere($column, 'ILIKE', "%{$search}%");
                }
            });
        }

        return $this;
    }

    /**
     * Apply sorting to query.
     */
    public function applySorting(): static
    {
        $sortBy = $this->request->query('sort_by', $this->defaultSort);
        $sortDirection = $this->request->query('sort_direction', $this->defaultDirection);

        if (in_array($sortBy, $this->sortableColumns)) {
            $this->query->orderBy($sortBy, $sortDirection);
        } else {
            $this->query->orderBy($this->defaultSort, $this->defaultDirection);
        }

        return $this;
    }

    /**
     * Get paginated results.
     */
    public function paginate()
    {
        $perPage = min($this->request->query('per_page', $this->perPage), 100);
        
        return $this->query->paginate($perPage);
    }

    /**
     * Add filter to datatable.
     */
    protected function addFilter(DataTableFilter $filter): static
    {
        $this->filters[] = $filter;
        return $this;
    }

    /**
     * Add column to datatable.
     */
    protected function addColumn(string $key, string $label, array $options = []): static
    {
        $this->columns[] = array_merge([
            'key' => $key,
            'label' => $label,
            'sortable' => false,
            'searchable' => false,
        ], $options);

        if ($options['searchable'] ?? false) {
            $this->searchableColumns[] = $key;
        }

        if ($options['sortable'] ?? false) {
            $this->sortableColumns[] = $key;
        }

        return $this;
    }

    /**
     * Get datatable configuration.
     */
    public function getConfig(): array
    {
        return [
            'columns' => $this->columns,
            'filters' => collect($this->filters)->map->toArray(),
            'search' => [
                'enabled' => !empty($this->searchableColumns),
                'placeholder' => 'Search...',
            ],
            'sorting' => [
                'enabled' => !empty($this->sortableColumns),
                'default_field' => $this->defaultSort,
                'default_direction' => $this->defaultDirection,
            ],
            'pagination' => [
                'per_page' => $this->perPage,
                'per_page_options' => [10, 25, 50, 100],
            ],
        ];
    }

    /**
     * Process datatable request and return results.
     */
    public function process()
    {
        return $this->applyFilters()
                   ->applySearch()
                   ->applySorting()
                   ->paginate();
    }
}
