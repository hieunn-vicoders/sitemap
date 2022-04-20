<?php

namespace VCComponent\Laravel\Sitemap\Http\Controllers\Web;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Cache;
use Spatie\Sitemap\SitemapGenerator;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

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
        $driver = config('sitemap.driver', 'internal'); 
        if ($driver == 'internal') {
            return $this->internalGenerator();
        } elseif ($driver == 'external') {
            return $this->externalGenerator();
        }
        return 'Undefine sitemap driver.';
    }

    public function externalGenerator()
    {
        if (Cache::get('webpress-sitemap') && file_exists($this->sitemapFilePath)) {
            return response()->file($this->sitemapFilePath);
        } else {
            SitemapGenerator::create(config('app.url'))->writeToFile($this->sitemapFilePath);

            Cache::put('webpress-sitemap', true, now()->addDays(config('sitemap.cache.webpress', 1)));

            return response()->file($this->sitemapFilePath);
        }
    }

    public function internalGenerator()
    {
        if (Cache::get('webpress-sitemap') && file_exists($this->sitemapFilePath)) {
            return response()->file($this->sitemapFilePath);
        } else {
            $sitemap = Sitemap::create();

            $this->createPostsSitemap($sitemap);
            $this->createProductsSitemap($sitemap);
            $this->createDefaultSitemap($sitemap);
            
            $sitemap->writeToFile($this->sitemapFilePath);
            
            Cache::put('webpress-sitemap', true, now()->addDays(config('sitemap.cache.webpress', 1)));

            return response()->file($this->sitemapFilePath);
        }
    }

    protected function createPostsSitemap(Sitemap $sitemap)
    {
        $repository = app(\VCComponent\Laravel\Post\Repositories\PostRepository::class);
        $repository->getEntity()->where('status', 1)->get()->map(function ($post) use ($sitemap) {
            $sitemap->add(Url::create($post->type.'/'.$post->slug)
                ->setLastModificationDate($post->updated_at)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
                ->setPriority(0.8));
        });
    }

    protected function createProductsSitemap(Sitemap $sitemap)
    {
        $repository = app(\VCComponent\Laravel\Product\Repositories\ProductRepository::class);
        $repository->getEntity()->where('status', 1)->get()->map(function ($product) use ($sitemap) {
            $sitemap->add(Url::create($product->product_type.'/'.$product->slug)
                ->setLastModificationDate($product->updated_at)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
                ->setPriority(0.8));
        });
    }

    protected function createDefaultSitemap(Sitemap $sitemap)
    {
        $urls = config('sitemap.default_urls');
        if (is_array($urls)) {
            foreach ($urls as $url) {
                $sitemap->add(Url::create($url['loc'])   
                    ->setLastModificationDate($url['lastmod'])
                    ->setChangeFrequency($url['changefreq'])
                    ->setPriority($url['priority']));
            }
        }
    }
}
