<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /** The app root redirects to /home (see routes/web.php for why), and the home page renders. */
    public function test_the_application_returns_a_successful_response(): void
    {
        $this->withoutVite();

        $this->get('/')->assertRedirect(route('home'));
        $this->get('/home')->assertStatus(200);
    }
}
