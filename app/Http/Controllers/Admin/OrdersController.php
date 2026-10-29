<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class OrdersController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('search', ''));
        $status = trim((string) $request->query('status', ''));

        $query = Order::query()
            ->with(['items.product:id,main_image'])
            ->latest('created_at');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', '%'.$search.'%')
                    ->orWhere('customer_name', 'like', '%'.$search.'%')
                    ->orWhere('customer_phone', 'like', '%'.$search.'%')
                    ->orWhere('customer_city', 'like', '%'.$search.'%');
            });
        }

        if ($status !== '') {
            $query->where('status', $status);
        }

        $orders = $query
            ->paginate(10)
            ->withQueryString()
            ->through(fn (Order $order): array => [
                ...$order->only(['id', 'order_number', 'customer_name', 'customer_phone', 'customer_city', 'customer_address', 'customer_notes', 'subtotal', 'delivery_fee', 'total', 'status', 'created_at']),
                'items' => $order->items->map(fn ($item): array => [
                    ...$item->only(['id', 'product_name', 'unit_price', 'quantity', 'line_total']),
                    'image_url' => $this->productImageUrl($item->product?->main_image),
                ])->values(),
            ]);

        $statuses = Order::query()
            ->whereNotNull('status')
            ->distinct()
            ->orderBy('status')
            ->pluck('status')
            ->values();

        $stats = [
            'total' => Order::query()->count(),

            'pending' => Order::query()
                ->where('status', 'pending')
                ->count(),

            'delivered' => Order::query()
                ->where('status', 'delivered')
                ->count(),

            'revenue' => (string) Order::query()
                ->where('status', 'delivered')
                ->sum('total'),
        ];

        return Inertia::render('admin/orders/Index', [
            'orders' => $orders,
            'filters' => [
                'search' => $search,
                'status' => $status,
            ],
            'statuses' => $statuses,
            'stats' => $stats,
        ]);
    }

    private function productImageUrl(?string $image): ?string
    {
        if (! $image) {
            return null;
        }

        return str_starts_with($image, 'https://') || str_starts_with($image, '/')
            ? $image
            : Storage::disk('public')->url($image);
    }
}
