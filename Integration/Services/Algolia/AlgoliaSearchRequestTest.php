<?php

namespace Tests\Integration\Services\Algolia;

use Illuminate\Validation\ValidationException;
use MotionArray\Services\Algolia\AlgoliaSearchRequest;
use Tests\TestCase;

class AlgoliaSearchRequestTest extends TestCase
{
    public function testConvertingLegacyCategorySlugs()
    {
        $data = [
            'categoryFilters' => [
                'stock-music' => [
                    'name' => 'Royalty Free Music',
                    'slug' => 'stock-music',
                    'version' => [],
                    'subCategories' => [],
                    'bpms' => [],
                    'durations' => []
                ]
            ],
        ];

        $request = new AlgoliaSearchRequest($data);

        $attributes = $request->attributes();
        $this->assertTrue(array_key_exists('royalty-free-music', $attributes['categoryFilters']));
        $this->assertFalse(array_key_exists('stock-music', $attributes['categoryFilters']));
    }


    public function testValidatingInvalidCategorySlugs()
    {
        $data = [
            'categoryFilters' => [
                'invalid-slug' => [
                    'name' => 'Royalty Free Music',
                    'slug' => 'stock-music',
                    'version' => [],
                    'subCategories' => [],
                    'bpms' => [],
                    'durations' => []
                ]
            ],
        ];

        $request = new AlgoliaSearchRequest($data);

        try {
            $attributes = $request->attributes();
            $this->fail('failed to validate');
        } catch (ValidationException $e) {
            $this->assertEquals([
                'categoryFilter' => [
                    'The following category slugs are invalid: invalid-slug'
                ]
            ], $e->errors());
        }
    }
}
