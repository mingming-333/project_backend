<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testPrivacyPolicyPage()
    {
        $response = $this->get('/privacyPolicy');

        $response->assertStatus(200);
    }
}
