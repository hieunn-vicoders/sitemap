<?php

namespace VCComponent\Laravel\Sitemap\Http\Controllers\Web;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Cache;
use Spatie\Sitemap\SitemapGenerator;

class SitemapController extends BaseController
{
    public function __construct()
    {

        if (config('sitemap.file.sitemap') !== null) {
            $this->sitemapFilePath = config('sitemap.file.sitemap');
        } else {
            $this->sitemapFilePath = storage_path('sitemap\sitemap.xml');
        }
    }

    public function __invoke()
    {
        if (Cache::get('webpress-sitemap') && file_exists($this->sitemapFilePath)) {
            return response()->file($this->sitemapFilePath);
        } else {
            SitemapGenerator::create(config('app.url'))->writeToFile($this->sitemapFilePath);

            Cache::put('webpress-sitemap', true, now()->addDays(config('sitemap.cache.webpress', 1)));

            return response()->file($this->sitemapFilePath);
        }
    }
}
