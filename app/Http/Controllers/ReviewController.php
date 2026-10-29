<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReviewRequest;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;

class ReviewController extends Controller
{
    public function store(StoreReviewRequest $request, Product $product): RedirectResponse
    {
        abort_unless(Schema::hasTable('reviews'), 503, 'قسم الآراء غير متوفر حالياً.');
        abort_unless($product->is_active && $product->deleted_at === null, 404);

        $product->reviews()->create([
            ...$request->safe()->except('website'),
            'status' => 'pending',
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'شكراً لكِ. سيظهر رأيكِ بعد مراجعته.']);

        return back();
    }
}
