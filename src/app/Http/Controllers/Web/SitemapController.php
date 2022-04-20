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
            $this->sitemap = config('sitemap.file.sitemap');
        } else {
            $this->sitemap = storage_path('sitemap\sitemap.xml');
        }
    }

    public function __invoke()
    {
        if (Cache::get('webpress-sitemap') && file_exists($this->sitemap)) {
            //Nếu tồn tại file sitemap và cache của sitemap có giá trị, thì trả về sitemap
            return response()->file($this->sitemap);
        } else {
            //Nếu không có file sitemap hoặc cache của sitemap đã hết hạn (Không có giá trị), thì sẽ tự tạo một file sitemap mới
            SitemapGenerator::create(config('app.url'))->writeToFile($this->sitemap);
            //code...
            
            //Khởi tạo cache cho sitemap, với thời gian hết hạn.
            Cache::put('webpress-sitemap', true, now()->addDays(config('sitemap.cache.webpress', 1)));

            //Trả về response file sitemap
            return response()->file($this->sitemap);
        }
    }
}
