<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReorderProductImagesRequest;
use App\Http\Requests\Admin\StoreProductImagesRequest;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Throwable;

class ProductImageController extends Controller
{
    public function store(StoreProductImagesRequest $request, Product $product): RedirectResponse
    {
        $images = $request->file('images', []);

        if ($product->images()->count() + count($images) > 8) {
            throw ValidationException::withMessages([
                'images' => 'Un produit ne peut pas contenir plus de 8 images.',
            ]);
        }

        $sortOrder = (int) $product->images()->max('sort_order');
        $storedPaths = [];

        try {
            DB::transaction(function () use ($images, $product, $sortOrder, &$storedPaths): void {
                /** @var UploadedFile $image */
                foreach ($images as $image) {
                    $path = $image->store("products/{$product->id}", 'public');
                    $storedPaths[] = $path;

                    $product->images()->create([
                        'image_path' => $path,
                        'sort_order' => ++$sortOrder,
                    ]);

                    if (! $product->main_image) {
                        $product->update(['main_image' => $path]);
                    }
                }
            });
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($storedPaths);

            throw $exception;
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Images ajoutées.']);

        return back();
    }

    public function destroy(Product $product, ProductImage $productImage): RedirectResponse
    {
        $this->ensureImageBelongsToProduct($product, $productImage);

        $path = $productImage->image_path;
        $wasMainImage = $product->main_image === $path;

        $productImage->delete();

        if ($wasMainImage) {
            $product->update([
                'main_image' => $product->images()->orderBy('sort_order')->value('image_path'),
            ]);
        }

        Storage::disk('public')->delete($path);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Image supprimée.']);

        return back();
    }

    public function makeMain(Product $product, ProductImage $productImage): RedirectResponse
    {
        $this->ensureImageBelongsToProduct($product, $productImage);
        $product->update(['main_image' => $productImage->image_path]);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Image principale mise à jour.']);

        return back();
    }

    public function reorder(ReorderProductImagesRequest $request, Product $product): RedirectResponse
    {
        foreach ($request->validated('image_ids') as $index => $imageId) {
            $product->images()->whereKey($imageId)->update(['sort_order' => $index]);
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Ordre des images mis à jour.']);

        return back();
    }

    private function ensureImageBelongsToProduct(Product $product, ProductImage $productImage): void
    {
        abort_unless($productImage->product_id === $product->id, 404);
    }
}
