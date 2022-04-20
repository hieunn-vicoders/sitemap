<?php

Route::prefix(config('sitemap.namespace'))->middleware('web')
    ->group(function () {
        Route::get('/sitemap.xml', 'VCComponent\Laravel\Sitemap\Http\Controllers\Web\SitemapController');
        Route::get('/sitemap/generate/internal', 'VCComponent\Laravel\Sitemap\Http\Controllers\Web\SitemapController@InternalGenerator');
        Route::get('/sitemap/generate/external', 'VCComponent\Laravel\Sitemap\Http\Controllers\Web\SitemapController@ExternalGenerator');
    });
