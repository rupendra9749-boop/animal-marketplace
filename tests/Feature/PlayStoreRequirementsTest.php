<?php

namespace Tests\Feature;

use App\Models\Animal;
use App\Models\Caretaker;
use App\Models\User;
use App\Models\Vet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** What Google Play asks of an app with accounts: a privacy policy, and account deletion in the app and on the web. */
class PlayStoreRequirementsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_the_privacy_policy_and_deletion_pages_are_public_in_both_languages(): void
    {
        $this->get('/privacy')->assertOk()->assertSee('Privacy policy')->assertSee('Location');
        $this->get('/delete-account')->assertOk()->assertSee('Delete Account');
        $this->get('/home')->assertSee(route('privacy'), false)->assertSee(route('account.deletion'), false);

        $this->get('/language/hi');
        $this->get('/privacy')->assertOk()->assertSee('प्राइवेसी पॉलिसी');
        $this->get('/delete-account')->assertOk()->assertSee('खाता हटाएँ');
    }

    public function test_deleting_an_account_removes_listings_profiles_and_photos(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('animals/a.png', 'x');
        Storage::disk('public')->put('vets/v.png', 'x');
        Storage::disk('public')->put('caretakers/c.png', 'x');

        $user = User::factory()->seller()->create();
        $animal = Animal::factory()->create(['user_id' => $user->id, 'image_path' => 'animals/a.png']);
        $vet = Vet::factory()->create(['user_id' => $user->id, 'photo_path' => 'vets/v.png', 'is_active' => true]);
        $caretaker = Caretaker::factory()->create(['user_id' => $user->id, 'photo_path' => 'caretakers/c.png', 'is_active' => true]);

        $this->actingAs($user)->delete('/profile', ['password' => 'password'])->assertRedirect(route('home'));

        $this->assertGuest();
        $this->assertModelMissing($user);
        $this->assertModelMissing($animal);
        $this->assertModelMissing($vet);
        $this->assertModelMissing($caretaker);
        Storage::disk('public')->assertMissing(['animals/a.png', 'vets/v.png', 'caretakers/c.png']);
    }
}
