<?php

namespace VCComponent\Laravel\Sitemap\Test\Feature\Api;

use Illuminate\Foundation\Auth\User;
use VCComponent\Laravel\Sitemap\Test\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class SitemapControllerTest extends TestCase
{

    /** @test */
    public function can_upload_sitemap()
    {
        $user = factory(User::class)->create();

        $base_url = url()->full();

        Storage::fake('sitemaps');

        $file = UploadedFile::fake()->create('sitemap.html');

        $prefix = $this->app['config']->get('sitemap.namespace');

        $response = $this->actingAs($user)->post('api/' . $prefix . '/sitemap', ['sitemap' => $file]);

        // $response->assertStatus(200);
        $response->assertJson(['url' => $base_url . '/sitemap.xml']);

        Storage::disk('sitemaps')->assertExists('sitemap.xml');
    }

    /** @test */
    public function should_not_upload_without_file_sitemap()
    {
        $user = factory(User::class)->create();

        $file = UploadedFile::fake()->create('sitemap.html');

        $prefix = $this->app['config']->get('sitemap.namespace');

        $response = $this->actingAs($user)->post('api/' . $prefix . '/sitemap', []);

        $response->assertStatus(422);
        $response->assertJson([
            'message' => 'The given data was invalid.',
            'errors'  => [
                'sitemap' => []
            ]
        ]);
    }

    /** @test */
    public function can_upload_a_new_sitemap()
    {
        $user = factory(User::class)->create();

        $base_url = url()->full();

        Storage::fake('sitemaps');

        $existed_file = UploadedFile::fake()->create('sitemap.xml', "current_sitemap");
        Storage::disk('sitemaps')->putFileAs('', $existed_file, $existed_file->getClientOriginalName());

        $file = UploadedFile::fake()->create('sitemap.html');

        $prefix = $this->app['config']->get('sitemap.namespace');

        $response = $this->actingAs($user)->post('api/' . $prefix . '/sitemap', ['sitemap' => $file]);

        $response->assertStatus(200);
        $response->assertJson(['url' => $base_url . '/sitemap.xml']);

        Storage::disk('sitemaps')->assertExists('sitemap.xml');
        Storage::disk('sitemaps')->assertExists('sitemap.xml.bak');
        Storage::disk('sitemaps')->assertExists('sitemap-' . date('Y-m-d') . '.xml');
    }

    /** @test */
    public function can_rollback_sitemap_bak()
    {
        $user = factory(User::class)->create();

        Storage::fake('sitemaps');

        $existed_file = UploadedFile::fake()->create('sitemap.xml', "current_sitemap");
        Storage::disk('sitemaps')->putFileAs('', $existed_file, $existed_file->getClientOriginalName());

        $existed_file_bak = UploadedFile::fake()->create('sitemap.xml.bak', "backup_sitemap");
        Storage::disk('sitemaps')->putFileAs('', $existed_file_bak, $existed_file_bak->getClientOriginalName());

        $prefix = $this->app['config']->get('sitemap.namespace');

        $response = $this->actingAs($user)->put('api/' . $prefix . '/sitemap');

        $response->assertStatus(200);

        $this->assertEquals('backup_sitemap', Storage::disk('sitemaps')->get('sitemap.xml'));
        Storage::disk('sitemaps')->assertExists('sitemap.xml');
    }

    /** @test */
    public function should_not_rollback_sitemap_bak_without_sitemap()
    {
        $user = factory(User::class)->create();

        Storage::fake('sitemaps');

        $prefix = $this->app['config']->get('sitemap.namespace');

        $response = $this->actingAs($user)->put('api/' . $prefix . '/sitemap');

        $response->assertStatus(200);
    }

    /** @test */
    public function should_not_rollback_sitemap_bak_without_sitemap_bak()
    {
        $user = factory(User::class)->create();

        Storage::fake('sitemaps');

        $existed_file = UploadedFile::fake()->create('sitemap.xml', "current_sitemap");
        Storage::disk('sitemaps')->putFileAs('', $existed_file, $existed_file->getClientOriginalName());

        $prefix = $this->app['config']->get('sitemap.namespace');

        $response = $this->actingAs($user)->put('api/' . $prefix . '/sitemap');

        $response->assertStatus(200);

        $this->assertEquals('current_sitemap', Storage::disk('sitemaps')->get('sitemap.xml'));
        Storage::disk('sitemaps')->assertExists('sitemap.xml');
    }
}
