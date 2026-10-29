<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateReviewRequest;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ReviewController extends Controller
{
    public function index(): Response
    {
        $reviews = Review::query()
            ->with('product:id,name')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('admin/reviews/Index', [
            'reviews' => $reviews,
        ]);
    }

    public function update(UpdateReviewRequest $request, Review $review): RedirectResponse
    {
        $review->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Le statut de l’avis a été mis à jour.']);

        return back();
    }

    public function destroy(Review $review): RedirectResponse
    {
        $review->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'L’avis a été supprimé.']);

        return back();
    }
}
