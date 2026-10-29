<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use App\Models\StoreSetting;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;

class InformationPageController extends Controller
{
    /** @var array<string, array{title: string, key: string}> */
    private const PAGES = [
        'a-propos' => ['title' => 'À propos de NATÉ', 'key' => 'about_content'],
        'conditions-generales' => ['title' => 'Conditions générales', 'key' => 'terms_content'],
        'confidentialite' => ['title' => 'Politique de confidentialité', 'key' => 'privacy_content'],
        'retours' => ['title' => 'Retours et échanges', 'key' => 'returns_content'],
        'livraison' => ['title' => 'Livraison et paiement', 'key' => 'delivery_terms'],
    ];

    public function show(string $page): Response
    {
        abort_unless(isset(self::PAGES[$page]), 404);
        $definition = self::PAGES[$page];
        $content = null;

        if (Schema::hasTable('store_settings')) {
            $content = StoreSetting::query()->where('key', $definition['key'])->value('value');

            if ($page === 'livraison') {
                $delivery = StoreSetting::values(['delivery_terms', 'delivery_zones', 'delivery_fee']);
                $content = collect([
                    $delivery['delivery_terms'],
                    filled($delivery['delivery_zones']) ? "Zones desservies :\n{$delivery['delivery_zones']}" : null,
                    filled($delivery['delivery_fee']) ? "Frais de livraison : {$delivery['delivery_fee']} MAD" : null,
                    'Mode de paiement : paiement à la livraison.',
                ])->filter()->implode("\n\n");
            }
        }

        return Inertia::render('InformationPage', [
            'title' => $definition['title'],
            'content' => $content,
        ]);
    }

    public function faq(): Response
    {
        return Inertia::render('Faq', [
            'faqs' => Schema::hasTable('faqs')
                ? Faq::query()->where('is_active', true)->orderBy('sort_order')->orderBy('id')->get(['id', 'question', 'answer'])
                : [],
        ]);
    }
}
