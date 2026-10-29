<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateStoreContentRequest;
use App\Models\Faq;
use App\Models\StoreSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;

class StoreContentController extends Controller
{
    private const KEYS = [
        'whatsapp', 'instagram', 'phone', 'email', 'delivery_fee', 'delivery_zones',
        'delivery_terms', 'about_content', 'terms_content', 'privacy_content', 'returns_content',
    ];

    public function edit(): Response
    {
        return Inertia::render('admin/content/Edit', [
            'settings' => Schema::hasTable('store_settings') ? StoreSetting::values(self::KEYS) : [],
            'faqs' => Schema::hasTable('faqs') ? Faq::query()->orderBy('sort_order')->orderBy('id')->get() : [],
            'databaseReady' => Schema::hasTable('store_settings') && Schema::hasTable('faqs'),
        ]);
    }

    public function update(UpdateStoreContentRequest $request): RedirectResponse
    {
        abort_unless(Schema::hasTable('store_settings'), 503, 'La migration du contenu boutique doit être exécutée.');
        StoreSetting::putMany($request->safe()->only(self::KEYS));
        Inertia::flash('toast', ['type' => 'success', 'message' => 'Informations de la boutique mises à jour.']);

        return back();
    }
}
