<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Root URL redirect ke halaman tickets (aplikasi utama).
     */
    public function test_root_redirects_to_tickets(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/tickets');
    }
}
