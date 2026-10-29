<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreFaqRequest;
use App\Http\Requests\Admin\UpdateFaqRequest;
use App\Models\Faq;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class FaqController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function store(StoreFaqRequest $request): RedirectResponse
    {
        Faq::query()->create($request->validated());
        Inertia::flash('toast', ['type' => 'success', 'message' => 'Question ajoutée.']);

        return back();
    }

    /**
     * Display the specified resource.
     */
    public function update(UpdateFaqRequest $request, Faq $faq): RedirectResponse
    {
        $faq->update($request->validated());
        Inertia::flash('toast', ['type' => 'success', 'message' => 'Question mise à jour.']);

        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Faq $faq): RedirectResponse
    {
        $faq->delete();
        Inertia::flash('toast', ['type' => 'success', 'message' => 'Question supprimée.']);

        return back();
    }
}
