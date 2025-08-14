<?php

namespace LaravelShopper\DataTable;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

abstract class DataTableFilter
{
    protected string $key;
    protected string $label;
    protected string $type;
    protected array $options = [];

    public function __construct(string $key, string $label, string $type = 'select')
    {
        $this->key = $key;
        $this->label = $label;
        $this->type = $type;
    }

    /**
     * Apply filter to query.
     */
    abstract public function apply(Builder $query, Request $request): void;

    /**
     * Set filter options.
     */
    public function options(array $options): static
    {
        $this->options = $options;
        return $this;
    }

    /**
     * Set options URL for dynamic loading.
     */
    public function optionsUrl(string $url): static
    {
        $this->options = ['url' => $url];
        return $this;
    }

    /**
     * Convert filter to array.
     */
    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'label' => $this->label,
            'type' => $this->type,
            'options' => $this->options,
        ];
    }

    /**
     * Get filter value from request.
     */
    protected function getValue(Request $request)
    {
        return $request->query("filter_{$this->key}");
    }

    /**
     * Check if filter has value.
     */
    protected function hasValue(Request $request): bool
    {
        $value = $this->getValue($request);
        return !is_null($value) && $value !== '';
    }
}

/**
 * Select filter implementation.
 */
class SelectFilter extends DataTableFilter
{
    public function __construct(string $key, string $label)
    {
        parent::__construct($key, $label, 'select');
    }

    public function apply(Builder $query, Request $request): void
    {
        if ($this->hasValue($request)) {
            $query->where($this->key, $this->getValue($request));
        }
    }
}

/**
 * Multi-select filter implementation.
 */
class MultiSelectFilter extends DataTableFilter
{
    public function __construct(string $key, string $label)
    {
        parent::__construct($key, $label, 'multi_select');
    }

    public function apply(Builder $query, Request $request): void
    {
        $values = $this->getValue($request);
        
        if (is_array($values) && !empty($values)) {
            $query->whereIn($this->key, $values);
        }
    }
}

/**
 * Date range filter implementation.
 */
class DateRangeFilter extends DataTableFilter
{
    public function __construct(string $key, string $label)
    {
        parent::__construct($key, $label, 'date_range');
    }

    public function apply(Builder $query, Request $request): void
    {
        $range = $this->getValue($request);
        
        if (is_array($range) && count($range) === 2) {
            [$start, $end] = $range;
            
            if ($start) {
                $query->whereDate($this->key, '>=', $start);
            }
            
            if ($end) {
                $query->whereDate($this->key, '<=', $end);
            }
        }
    }
}

/**
 * Number range filter implementation.
 */
class NumberRangeFilter extends DataTableFilter
{
    public function __construct(string $key, string $label)
    {
        parent::__construct($key, $label, 'number_range');
    }

    public function apply(Builder $query, Request $request): void
    {
        $range = $this->getValue($request);
        
        if (is_array($range)) {
            $min = $range['min'] ?? null;
            $max = $range['max'] ?? null;
            
            if ($min !== null) {
                $query->where($this->key, '>=', $min);
            }
            
            if ($max !== null) {
                $query->where($this->key, '<=', $max);
            }
        }
    }

    public function min(int $value): static
    {
        $this->options['min'] = $value;
        return $this;
    }

    public function max(int $value): static
    {
        $this->options['max'] = $value;
        return $this;
    }
}

/**
 * Boolean filter implementation.
 */
class BooleanFilter extends DataTableFilter
{
    public function __construct(string $key, string $label)
    {
        parent::__construct($key, $label, 'boolean');
    }

    public function apply(Builder $query, Request $request): void
    {
        $value = $this->getValue($request);
        
        if ($value !== null) {
            $query->where($this->key, filter_var($value, FILTER_VALIDATE_BOOLEAN));
        }
    }
}

/**
 * Custom filter implementation.
 */
class CustomFilter extends DataTableFilter
{
    protected $callback;

    public function __construct(string $key, string $label, callable $callback)
    {
        parent::__construct($key, $label, 'custom');
        $this->callback = $callback;
    }

    public function apply(Builder $query, Request $request): void
    {
        if ($this->hasValue($request)) {
            call_user_func($this->callback, $query, $this->getValue($request), $request);
        }
    }
}
