<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FaqController as AdminFaqController;
use App\Http\Controllers\Admin\OrdersController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductImageController;
use App\Http\Controllers\Admin\ProductTypeController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Admin\StoreContentController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\InformationPageController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\StorefrontController;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/dashboard', fn (): RedirectResponse => redirect()->route('admin.dashboard'))
        ->name('dashboard');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('dashboard', DashboardController::class)->name('dashboard');
        Route::get('orders', [OrdersController::class, 'index'])->name('orders');

        Route::patch('categories/{category}/toggle', [CategoryController::class, 'toggle'])
            ->name('categories.toggle');
        Route::resource('categories', CategoryController::class)->except('show');

        Route::patch('product-types/{product_type}/toggle', [ProductTypeController::class, 'toggle'])
            ->name('product-types.toggle');
        Route::resource('product-types', ProductTypeController::class)
            ->parameters(['product-types' => 'product_type'])
            ->except('show');

        Route::post('products/{product}/images', [ProductImageController::class, 'store'])
            ->name('products.images.store');
        Route::patch('products/{product}/images/reorder', [ProductImageController::class, 'reorder'])
            ->name('products.images.reorder');
        Route::patch('products/{product}/images/{productImage}/main', [ProductImageController::class, 'makeMain'])
            ->name('products.images.main');
        Route::delete('products/{product}/images/{productImage}', [ProductImageController::class, 'destroy'])
            ->name('products.images.destroy');
        Route::patch('products/{product}/slider-visibility', [ProductController::class, 'updateSliderVisibility'])
            ->name('products.slider-visibility.update');
        Route::resource('products', ProductController::class)->except('show');
        Route::get('reviews', [AdminReviewController::class, 'index'])->name('reviews.index');
        Route::patch('reviews/{review}', [AdminReviewController::class, 'update'])->name('reviews.update');
        Route::delete('reviews/{review}', [AdminReviewController::class, 'destroy'])->name('reviews.destroy');
        Route::get('content', [StoreContentController::class, 'edit'])->name('content.edit');
        Route::patch('content', [StoreContentController::class, 'update'])->name('content.update');
        Route::post('faqs', [AdminFaqController::class, 'store'])->name('faqs.store');
        Route::patch('faqs/{faq}', [AdminFaqController::class, 'update'])->name('faqs.update');
        Route::delete('faqs/{faq}', [AdminFaqController::class, 'destroy'])->name('faqs.destroy');
    });
});

Route::get('/', [StorefrontController::class, 'index'])->name('home');
Route::post('/panier', [StorefrontController::class, 'add'])->name('cart.add');
Route::patch('/panier/{product}', [StorefrontController::class, 'update'])->name('cart.update');
Route::delete('/panier/{product}', [StorefrontController::class, 'remove'])->name('cart.remove');
Route::post('/produits/{product}/avis', [ReviewController::class, 'store'])
    ->middleware('throttle:3,10')
    ->name('reviews.store');
Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout.show');
Route::post('/checkout', [CheckoutController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('checkout.store');
Route::get('/commande/{orderNumber}/confirmation', [CheckoutController::class, 'confirmation'])
    ->name('checkout.confirmation');
Route::get('/informations/faq', [InformationPageController::class, 'faq'])->name('information.faq');
Route::get('/informations/{page}', [InformationPageController::class, 'show'])->name('information.show');

require __DIR__.'/settings.php';
