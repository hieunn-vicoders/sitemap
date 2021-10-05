<?php

namespace VCComponent\Laravel\Sitemap\Http\Controllers\Api;

use Exception;
use Illuminate\Http\Request;
use VCComponent\Laravel\Vicoders\Core\Controllers\ApiController;

class UploadSitemapController extends ApiController
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
            $this->sitemap = storage_path('sitemap/sitemap.xml');
        }
    }

    public function __invoke(Request $request)
    {
        $request->validate([
            'sitemap' => 'required',
        ]);

        $file_name   = "sitemap.xml";
        $path        = str_replace($file_name, '', $this->sitemap);

        if (file_exists($this->sitemap) == true) {
            $old_sitemap = $path . 'sitemap-' . date('Y-m-d') . '.xml';
            copy($this->sitemap, $this->sitemap . ".bak");
            rename($this->sitemap, $old_sitemap);
        }

        $create_file = $request->file('sitemap')->move($path, $file_name);
        $url         = url('/' . $file_name);

        return response()->json(['url' => $url]);
    }
}
