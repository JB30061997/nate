<?php

namespace Tests\Feature;

use App\Models\Faq;
use App\Models\StoreSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class StoreContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_verified_store_information(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)->patch(route('admin.content.update'), [
            'whatsapp' => '+212600000000', 'instagram' => 'https://instagram.com/nate',
            'phone' => '+212500000000', 'email' => 'contact@example.com',
            'delivery_fee' => '25.00', 'delivery_zones' => 'Casablanca',
            'delivery_terms' => 'Paiement à la livraison.', 'about_content' => 'Contenu vérifié.',
            'terms_content' => null, 'privacy_content' => null, 'returns_content' => null,
        ])->assertRedirect();

        $this->assertDatabaseHas('store_settings', ['key' => 'delivery_fee', 'value' => '25.00']);
        $this->assertDatabaseHas('store_settings', ['key' => 'about_content', 'value' => 'Contenu vérifié.']);
    }

    public function test_non_admin_cannot_update_store_information(): void
    {
        $this->actingAs(User::factory()->create())
            ->patch(route('admin.content.update'), ['delivery_fee' => '10.00'])
            ->assertForbidden();
    }

    public function test_only_active_faqs_are_public(): void
    {
        Faq::factory()->create(['question' => 'Question visible ?', 'is_active' => true]);
        Faq::factory()->create(['question' => 'Question masquée ?', 'is_active' => false]);

        $this->get(route('information.faq'))->assertInertia(fn (Assert $page) => $page
            ->component('Faq')->has('faqs', 1)->where('faqs.0.question', 'Question visible ?'));
    }

    public function test_information_page_uses_only_saved_content(): void
    {
        StoreSetting::query()->create(['key' => 'privacy_content', 'value' => 'Politique validée.']);
        $this->get(route('information.show', 'confidentialite'))->assertInertia(fn (Assert $page) => $page
            ->component('InformationPage')->where('content', 'Politique validée.'));
    }
}
