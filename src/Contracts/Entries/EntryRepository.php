<?php

declare(strict_types=1);

namespace Cartino\Contracts\Entries;

interface EntryRepository
{
    /**
     * Find an entry by ID
     *
     * @param  string  $id
     * @return \Cartino\Contracts\Entries\Entry|null
     */
    public function find($id);

    /**
     * Find an entry by slug
     *
     * @param  string  $slug
     * @param  string  $collection
     * @return \Cartino\Contracts\Entries\Entry|null
     */
    public function findBySlug($slug, $collection);

    /**
     * Get all entries in a collection
     *
     * @param  string  $collection
     * @return \Illuminate\Support\Category
     */
    public function whereCollection($collection);

    /**
     * Save an entry
     *
     * @param  \Cartino\Contracts\Entries\Entry  $entry
     * @return bool
     */
    public function save($entry);

    /**
     * Delete an entry
     *
     * @param  \Cartino\Contracts\Entries\Entry  $entry
     * @return bool
     */
    public function delete($entry);

    /**
     * Create a new entry instance
     *
     * @return \Cartino\Contracts\Entries\Entry
     */
    public function make();

    /**
     * Query entries
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query();
}
