<?php

namespace Tests\Integration\Services\Algolia;

use MotionArray\Services\Algolia\AlgoliaResponseParserForSite;
use Tests\TestCase;

class AlgoliaResponseParserTest extends TestCase
{
    public function testParsingLegacyCategorySlugs()
    {
        $parser = app(AlgoliaResponseParserForSite::class);
        $results = [
            'index' => 'test-index',
            'searchTags' => [],
            'options' => [],
            'total' => 120,
            'currentPage' => 1,
            'perPage' => 60,
            'products' => [
                [
                    'objectID' => '91146',
                    'audio_placeholder' => null,
                    'category' => [
                        'id' => 3,
                        'slug' => 'stock-music',
                        'name' => 'Music',
                        'short_name' => 'Music',
                    ],
                    'description' => 'test',
                    'downloads' => 0,
                    'free' => false,
                    'is_kick_ass' => false,
                    'is_music' => false,
                    'is_new' => false,
                    'name' => 'Requested SMG - LOL cats',
                    'owned_by_ma' => false,
                    'placeholder' => 'http://qa--previews.s3.amazonaws.com/preview-91146-kTolF2LzeP-low_0006.jpg',
                    'placeholder_fallback' => 'http://qa--previews.s3.amazonaws.com/preview-91146-kTolF2LzeP-low_0006.jpg',
                    'previews_files' => [
                        [
                            'format' => 'mpeg4',
                            'label' => 'webm low',
                            'url' => 'http://qa--previews.s3.amazonaws.com/preview-91146-kTolF2LzeP-low.webm',
                            'url_fallback' => 'http://qa--previews.s3.amazonaws.com/preview-91146-kTolF2LzeP-low.webm',
                        ]
                    ],
                    'published_at' => 1547717625,
                    'requested' => true,
                    'seller_id' => 14,
                    'slug' => 'requested-smg-lol-cats-91146',
                    'url' => '/stock-motion-graphics/requested-smg-lol-cats-91146',
                ]
            ]
        ];

        $output = $parser->responseToArray($results);

        $this->assertEquals('royalty-free-music', $output['products'][0]['category']['slug']);
    }
}
