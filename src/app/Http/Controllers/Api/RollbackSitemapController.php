<?php

namespace VCComponent\Laravel\Sitemap\Http\Controllers\Api;

use Exception;
use Illuminate\Http\Request;
use VCComponent\Laravel\Vicoders\Core\Controllers\ApiController;

class RollbackSitemapController extends ApiController
{
    public function __construct()
    {
        if (config('sitemap.auth_middleware.admin') !== null) {
            foreach (config('sitemap.auth_middleware.admin') as $middleware) {
                $this->middleware($middleware['middleware'], ['except' => $middleware['except']]);
            }
        } else {
            throw new Exception("Admin middleware configuration is required");
        }
        if (config('sitemap.file.sitemap') !== null) {
            $this->sitemap = config('sitemap.file.sitemap');
        } else {
            $this->sitemap = storage_path('sitemap\sitemap.xml');
        }
    }

    public function __invoke()
    {
        if (file_exists($this->sitemap) == false) {
            return "Site map does not exists !";
        }

        $file_name = "sitemap.xml";
        $path      = str_replace($file_name, '', $this->sitemap);

        if (file_exists($this->sitemap . ".bak")) {
            $old_sitemap = $path . 'sitemap-' . date('Y-m-d') . '.xml';
            copy($this->sitemap, $this->sitemap . ".trash");
            rename($this->sitemap, $old_sitemap);
            rename($this->sitemap . ".bak", $this->sitemap);
            rename($this->sitemap . ".trash", $this->sitemap . ".bak");
            return response()->json('true');
        } else {
            return response()->json('false');
        }
    }
}
