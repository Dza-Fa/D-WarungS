<?php

namespace Tests\Feature;

use Tests\TestCase;

class CheckoutTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_example(): void
    {
        $this->withoutExceptionHandling();
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
