<?php

declare(strict_types=1);

namespace Cartino\Tests\Unit;

use Cartino\Models\Product;
use Cartino\Models\Translation;
use Cartino\Services\LocaleResolver;
use Cartino\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TranslationTest extends TestCase
{
    use RefreshDatabase;

    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->product = Product::create([
            'name' => 'Test Product',
            'slug' => 'test-product',
            'description' => 'English description',
            'status' => 'active',
        ]);
    }

    /** @test */
    public function it_can_set_and_get_translation()
    {
        Translation::set($this->product, 'name', 'Prodotto Test', 'it_IT');

        $translation = Translation::get($this->product, 'name', 'it_IT');

        $this->assertEquals('Prodotto Test', $translation);
    }

    /** @test */
    public function it_returns_default_when_translation_not_found()
    {
        $translation = Translation::get($this->product, 'name', 'fr_FR', 'Default Name');

        $this->assertEquals('Default Name', $translation);
    }

    /** @test */
    public function it_can_remove_translation()
    {
        Translation::set($this->product, 'name', 'Prodotto Test', 'it_IT');

        $count = Translation::remove($this->product, 'name', 'it_IT');

        $this->assertEquals(1, $count);
        $this->assertNull(Translation::get($this->product, 'name', 'it_IT'));
    }

    /** @test */
    public function locale_resolver_builds_fallback_chain()
    {
        $resolver = app(LocaleResolver::class);

        config(['app.locale' => 'en', 'app.fallback_locale' => 'en']);

        $chain = $resolver->getFallbackChain('it_IT');

        // it_IT → it → en
        $this->assertContains('it', $chain);
        $this->assertContains('en', $chain);
    }

    /** @test */
    public function locale_resolver_normalizes_locale_format()
    {
        $resolver = app(LocaleResolver::class);

        $normalized = $resolver->normalize('it-IT');

        $this->assertEquals('it_IT', $normalized);
    }
}
