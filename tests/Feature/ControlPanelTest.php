<?php

use Cartino\Http\Controllers\CP\CollectionsController;
use Cartino\Http\Controllers\CP\EntriesController;

// Collections Tests
test('collections index returns collections data', function () {
    $controller = new CollectionsController;
    $request = request();

    $response = $controller->index($request);

    expect($response->getStatusCode())->toBe(200);

    $data = json_decode($response->getContent(), true);
    expect($data)->toHaveKey('collections');
    expect($data)->toHaveKey('sections');
});

test('collections can be filtered by section', function () {
    $controller = new CollectionsController;
    $request = request(['section' => 'ecommerce']);

    $response = $controller->index($request);
    $data = json_decode($response->getContent(), true);

    foreach ($data['collections'] as $collection) {
        expect($collection['section'])->toBe('ecommerce');
    }
});

test('collections can be searched', function () {
    $controller = new CollectionsController;
    $request = request(['search' => 'products']);

    $response = $controller->index($request);
    $data = json_decode($response->getContent(), true);

    expect($data['collections'])->not->toBeEmpty();
});

test('collection can be created', function () {
    $controller = new CollectionsController;
    $request = request()->merge([
        'title' => 'Test Category',
        'handle' => 'test-collection',
        'description' => 'A test collection',
        'section' => 'custom',
    ]);

    $response = $controller->store($request);

    expect($response->getStatusCode())->toBe(201);

    $data = json_decode($response->getContent(), true);
    expect($data)->toHaveKey('collection');
    expect($data['collection']['title'])->toBe('Test Category');
});

// Entries Tests
test('entries index returns paginated entries', function () {
    $controller = new EntriesController;
    $request = request();

    $response = $controller->index($request, 'products');

    expect($response->getStatusCode())->toBe(200);

    $data = json_decode($response->getContent(), true);
    expect($data)->toHaveKey('entries');
    expect($data)->toHaveKey('pagination');
    expect($data)->toHaveKey('filters');
});

test('entries can be filtered by status', function () {
    $controller = new EntriesController;
    $request = request(['status' => 'published']);

    $response = $controller->index($request, 'products');
    $data = json_decode($response->getContent(), true);

    foreach ($data['entries'] as $entry) {
        expect($entry['status'])->toBe('published');
    }
});

test('entries can be searched', function () {
    $controller = new EntriesController;
    $request = request(['search' => 'headphones']);

    $response = $controller->index($request, 'products');
    $data = json_decode($response->getContent(), true);

    expect($data['entries'])->not->toBeEmpty();
});

test('entry can be created', function () {
    $controller = new EntriesController;
    $request = request()->merge([
        'title' => 'Test Product',
        'slug' => 'test-product',
        'status' => 'draft',
        'is_featured' => false,
        'fields' => [
            'price' => 99.99,
            'stock_quantity' => 10,
        ],
    ]);

    $response = $controller->store($request, 'products');

    expect($response->getStatusCode())->toBe(201);

    $data = json_decode($response->getContent(), true);
    expect($data)->toHaveKey('entry');
    expect($data['entry']['title'])->toBe('Test Product');
});

test('bulk actions work on entries', function () {
    $controller = new EntriesController;
    $request = request()->merge([
        'action' => 'publish',
        'entries' => [1, 2, 3],
    ]);

    $response = $controller->bulkAction($request, 'products');

    expect($response->getStatusCode())->toBe(200);

    $data = json_decode($response->getContent(), true);
    expect($data)->toHaveKey('message');
    expect($data['affected_count'])->toBe(3);
});

// Architecture Tests
test('collections follow naming conventions', function () {
    $controller = new CollectionsController;
    $request = request();

    $response = $controller->index($request);
    $data = json_decode($response->getContent(), true);

    foreach ($data['collections'] as $collection) {
        // Check that handle is kebab-case
        expect($collection['handle'])->toMatch('/^[a-z0-9-]+$/');
        // Check required fields exist
        expect($collection)->toHaveKey('title');
        expect($collection)->toHaveKey('handle');
        expect($collection)->toHaveKey('section');
    }
});

test('entries have proper structure', function () {
    $controller = new EntriesController;
    $request = request();

    $response = $controller->index($request, 'products');
    $data = json_decode($response->getContent(), true);

    foreach ($data['entries'] as $entry) {
        expect($entry)->toHaveKey('id');
        expect($entry)->toHaveKey('title');
        expect($entry)->toHaveKey('status');
        expect($entry)->toHaveKey('collection_handle');
        expect($entry['status'])->toBeIn(['published', 'draft', 'scheduled', 'expired']);
    }
});

// Performance Tests
test('collections index is performant', function () {
    $start = microtime(true);

    $controller = new CollectionsController;
    $response = $controller->index(request());

    $executionTime = microtime(true) - $start;

    expect($executionTime)->toBeLessThan(1.0); // Less than 1 second
    expect($response->getStatusCode())->toBe(200);
});

test('entries pagination works correctly', function () {
    $controller = new EntriesController;
    $request = request(['per_page' => 2, 'page' => 1]);

    $response = $controller->index($request, 'products');
    $data = json_decode($response->getContent(), true);

    expect(count($data['entries']))->toBeLessThanOrEqual(2);
    expect($data['pagination']['per_page'])->toBe(2);
    expect($data['pagination']['current_page'])->toBe(1);
});
