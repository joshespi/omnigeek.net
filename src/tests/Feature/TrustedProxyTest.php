<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class TrustedProxyTest extends TestCase
{
    public function test_forwarded_https_makes_the_request_secure(): void
    {
        $this->get('/up', ['X-Forwarded-Proto' => 'https'])->assertOk();

        $this->assertTrue($this->app['request']->isSecure());
    }

    public function test_signed_upload_url_uses_https_behind_an_https_proxy(): void
    {
        $this->get('/', ['X-Forwarded-Proto' => 'https']);

        $url = URL::temporarySignedRoute('livewire.upload-file', now()->addMinutes(5));

        $this->assertStringStartsWith('https://', $url);
    }
}
