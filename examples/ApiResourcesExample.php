<?php

/**
 * Esempi di utilizzo delle API Resources estendibili con DTO pattern
 * Questo file mostra come utilizzare il nuovo sistema implementato
 */

namespace LaravelShopper\Examples;

use LaravelShopper\Http\Resources\ProductResource;
use LaravelShopper\Http\Resources\ProductCollection;
use LaravelShopper\Data\ProductDto;
use LaravelShopper\Models\Product;

class ApiResourcesExample
{
    /**
     * Esempio 1: Utilizzo base delle Resources
     */
    public function basicResourceUsage()
    {
        // Recupera un prodotto
        $product = Product::with(['category', 'brand', 'variants'])->first();
        
        // Crea la resource con data transformation automatica
        $resource = new ProductResource($product);
        
        // La resource utilizza il DTO internamente per la trasformazione
        return $resource->toArray(request());
        
        /* Output esempio:
        {
            "id": 1,
            "name": "T-shirt Premium",
            "handle": "t-shirt-premium",
            "price": "€29.99",
            "formatted_price": "€29.99",
            "status": "active",
            "is_active": true,
            "created_at": "2 hours ago",
            "category": {...},
            "brand": {...}
        }
        */
    }

    /**
     * Esempio 2: Collection con paginazione e meta data
     */
    public function collectionWithPagination()
    {
        // Recupera prodotti paginati
        $products = Product::with(['category', 'brand'])
            ->paginate(10);
        
        // Crea la collection
        $collection = new ProductCollection($products);
        
        return $collection->toArray(request());
        
        /* Output esempio:
        {
            "data": [...],
            "links": {...},
            "meta": {
                "current_page": 1,
                "total": 50,
                "per_page": 10,
                "active_filters": [],
                "active_sorting": null
            }
        }
        */
    }

    /**
     * Esempio 3: Resource con campi condizionali
     */
    public function conditionalFields()
    {
        $product = Product::first();
        
        // Include relazioni solo se richieste
        $resource = new ProductResource($product);
        
        // Con parametri URL: ?include=variants,media
        request()->merge(['include' => 'variants,media']);
        
        return $resource->toArray(request());
        
        /* Include variants e media solo se richiesti */
    }

    /**
     * Esempio 4: Estensione personalizzata della Resource
     */
    public function customResourceExtension()
    {
        // Esempio di resource estesa per esigenze specifiche
        class CustomProductResource extends ProductResource
        {
            protected function transformData($data): array
            {
                // Chiama la trasformazione base
                $transformed = parent::transformData($data);
                
                // Aggiungi campi personalizzati
                $transformed['custom_field'] = 'Custom value';
                $transformed['calculated_discount'] = $this->calculateDiscount();
                
                return $transformed;
            }
            
            private function calculateDiscount(): string
            {
                // Logica personalizzata
                return '10%';
            }
        }
        
        $product = Product::first();
        return new CustomProductResource($product);
    }

    /**
     * Esempio 5: Utilizzo DTO per validazione e trasformazione
     */
    public function dtoValidationExample()
    {
        // Dati in input (ad esempio da form)
        $inputData = [
            'name' => 'New Product',
            'price' => '29.99',
            'status' => 'active',
            'inventory_tracked' => '1', // string
            'weight' => '0.5',
        ];
        
        // Crea DTO dai dati
        $productDto = ProductDto::from($inputData);
        
        // Il DTO converte automaticamente i tipi
        assert($productDto->inventory_tracked === true); // boolean
        assert($productDto->weight === 0.5); // float
        
        // Validazione DTO
        $errors = $productDto->validate();
        if (!empty($errors)) {
            throw new \InvalidArgumentException('Validation failed: ' . implode(', ', $errors));
        }
        
        // Usa il DTO per creare/aggiornare il modello
        $product = Product::create($productDto->toArray());
        
        return new ProductResource($product);
    }

    /**
     * Esempio 6: DataTable con filtri personalizzati
     */
    public function dataTableWithFilters()
    {
        // Simula request con filtri (come Shopify)
        $request = new \Illuminate\Http\Request([
            'search' => 'premium',
            'status' => 'active',
            'category_id' => 5,
            'price_min' => 10,
            'price_max' => 100,
            'sort' => 'created_at',
            'direction' => 'desc',
            'per_page' => 25,
        ]);
        
        // Crea DataTable
        $dataTable = new \LaravelShopper\DataTable\ProductDataTable($request);
        
        // Elabora i dati con filtri
        $products = $dataTable->process();
        
        // Ritorna collection con meta data sui filtri
        return new ProductCollection($products);
        
        /* Output include:
        - Prodotti filtrati
        - Meta data sui filtri attivi
        - Configurazione filtri disponibili
        - Azioni bulk disponibili
        */
    }

    /**
     * Esempio 7: Schema JSON per validazione dinamica
     */
    public function schemaBasedValidation()
    {
        $schemaRepository = new \LaravelShopper\Schema\SchemaRepository();
        
        // Carica schema prodotti da JSON
        $schema = $schemaRepository->getCollection('products');
        
        // Costruisci regole di validazione dallo schema
        $validationRules = $this->buildValidationFromSchema($schema);
        
        // Esempio dati
        $data = [
            'name' => 'Test Product',
            'handle' => 'test-product',
            'price' => 29.99,
            'status' => 'active',
        ];
        
        // Valida usando le regole dello schema
        $validator = validator($data, $validationRules);
        
        if ($validator->fails()) {
            return ['errors' => $validator->errors()];
        }
        
        // Se valido, crea DTO e salva
        $productDto = ProductDto::from($data);
        $product = Product::create($productDto->toArray());
        
        return new ProductResource($product);
    }

    /**
     * Helper: costruisci regole di validazione da schema
     */
    private function buildValidationFromSchema($schema): array
    {
        $rules = [];
        
        if (!$schema || !isset($schema['fields'])) {
            return $rules;
        }
        
        foreach ($schema['fields'] as $field => $config) {
            if (!empty($config['validate'])) {
                $rules[$field] = is_array($config['validate']) 
                    ? $config['validate'] 
                    : explode('|', $config['validate']);
            } elseif ($config['required'] ?? false) {
                $rules[$field] = ['required'];
            }
        }
        
        return $rules;
    }
}
