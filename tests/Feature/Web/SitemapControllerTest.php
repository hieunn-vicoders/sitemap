<?php

namespace VCComponent\Laravel\Sitemap\Test\Feature\Web;

use VCComponent\Laravel\Sitemap\Test\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class SitemapControllerTest extends TestCase
{
    /** @test */
    public function can_generate_sitemap()
    {
        // Storage::fake('sitemaps');

        // $sitemap = UploadedFile::fake()->create('sitemap.xml', "current_sitemap");
        // Storage::disk('sitemaps')->putFileAs('', $sitemap, $sitemap->getClientOriginalName());

        $prefix = $this->app['config']->get('sitemap.namespace');

        $response = $this->call('GET', $prefix . '/sitemap.xml');

        $response->assertStatus(200);

        $this->assertEquals(
            $response->getFile()->getPathName(),
            config('sitemap.file.sitemap')
        );
    }

    /** @test */
    public function can_get_cached_sitemap()
    {
        Storage::fake('sitemaps');

        $sitemap = UploadedFile::fake()->create('sitemap.xml', "current_sitemap");
        Storage::disk('sitemaps')->putFileAs('', $sitemap, $sitemap->getClientOriginalName());

        Cache::put('webpress-sitemap', true, now()->addDays(config('sitemap.cache.webpress', 1)));

        $prefix = $this->app['config']->get('sitemap.namespace');

        $response = $this->call('GET', $prefix . '/sitemap.xml');

        $response->assertStatus(200);

        $this->assertEquals(
            str_replace('/','\\',$response->getFile()->getPathName()),
            str_replace('/','\\',Storage::disk('sitemaps')->path('sitemap.xml'))
        );
    }
}
