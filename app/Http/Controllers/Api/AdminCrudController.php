<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\SiteNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Admin CRUD API parity with the web admin panel:
 * coupons, categories, banners + customer notification broadcast.
 */
class AdminCrudController extends Controller
{
    private function guard(Request $request): void
    {
        abort_unless($request->user()?->isAdmin(), 403, 'Admin access required.');
    }

    // ----- Coupons -----

    public function coupons(Request $request)
    {
        $this->guard($request);

        return response()->json(['data' => Coupon::latest('id')
            ->paginate((int) $request->query('per_page', 20))
            ->items()]);
    }

    public function storeCoupon(Request $request)
    {
        $this->guard($request);

        $data = $request->validate([
            'code'             => ['required', 'string', 'max:50', 'unique:coupons,code'],
            'type'             => ['required', 'in:percentage,fixed'],
            'value'            => ['required', 'numeric', 'min:0.01'],
            'min_order_amount' => ['nullable', 'numeric', 'min:0'],
            'max_discount'     => ['nullable', 'numeric', 'min:0'],
            'usage_limit'      => ['nullable', 'integer', 'min:1'],
            'starts_at'        => ['nullable', 'date'],
            'expires_at'       => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_active'        => ['boolean'],
        ]);

        $coupon = Coupon::create([
            ...$data,
            'code' => strtoupper($data['code']),
            'is_active' => $data['is_active'] ?? true,
        ]);

        return response()->json(['message' => 'Coupon created.', 'data' => $coupon], 201);
    }

    public function updateCoupon(Request $request, Coupon $coupon)
    {
        $this->guard($request);

        $data = $request->validate([
            'type'             => ['sometimes', 'in:percentage,fixed'],
            'value'            => ['sometimes', 'numeric', 'min:0.01'],
            'min_order_amount' => ['nullable', 'numeric', 'min:0'],
            'max_discount'     => ['nullable', 'numeric', 'min:0'],
            'usage_limit'      => ['nullable', 'integer', 'min:1'],
            'expires_at'       => ['nullable', 'date'],
            'is_active'        => ['sometimes', 'boolean'],
        ]);

        $coupon->update($data);

        return response()->json(['message' => 'Coupon updated.', 'data' => $coupon->fresh()]);
    }

    public function destroyCoupon(Coupon $coupon)
    {
        $this->guard(request());

        $coupon->delete();

        return response()->json(['message' => 'Coupon deleted.']);
    }

    // ----- Categories -----

    public function categories(Request $request)
    {
        $this->guard($request);

        $categories = Category::with('children:id,parent_id,name,slug,is_active')
            ->whereNull('parent_id')
            ->orderBy('position')
            ->get();

        return response()->json(['data' => $categories]);
    }

    public function storeCategory(Request $request)
    {
        $this->guard($request);

        $data = $request->validate([
            'name'        => ['required', 'string', 'max:120'],
            'parent_id'   => ['nullable', 'exists:categories,id'],
            'description' => ['nullable', 'string', 'max:1000'],
            'position'    => ['nullable', 'integer', 'min:0'],
            'is_active'   => ['boolean'],
        ]);

        $category = Category::create([
            ...$data,
            'slug' => Str::slug($data['name']) . '-' . Str::lower(Str::random(4)),
            'is_active' => $data['is_active'] ?? true,
            'position' => $data['position'] ?? 0,
        ]);

        return response()->json(['message' => 'Category created.', 'data' => $category], 201);
    }

    public function updateCategory(Request $request, Category $category)
    {
        $this->guard($request);

        $data = $request->validate([
            'name'        => ['sometimes', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
            'position'    => ['sometimes', 'integer', 'min:0'],
            'is_active'   => ['sometimes', 'boolean'],
        ]);

        if (isset($data['name']) && $data['name'] !== $category->name) {
            $data['slug'] = Str::slug($data['name']) . '-' . Str::lower(Str::random(4));
        }

        $category->update($data);

        return response()->json(['message' => 'Category updated.', 'data' => $category->fresh()]);
    }

    public function destroyCategory(Category $category)
    {
        $this->guard(request());

        if ($category->children()->exists()) {
            return response()->json(['message' => 'Delete or move the subcategories first.'], 422);
        }

        if ($category->products()->exists()) {
            return response()->json(['message' => 'Category still has products — move them first.'], 422);
        }

        $category->delete();

        return response()->json(['message' => 'Category deleted.']);
    }

    // ----- Banners -----

    public function banners(Request $request)
    {
        $this->guard($request);

        return response()->json(['data' => Banner::orderBy('placement')
            ->orderBy('position')
            ->get()]);
    }

    public function storeBanner(Request $request)
    {
        $this->guard($request);

        $data = $request->validate([
            'title'     => ['required', 'string', 'max:150'],
            'subtitle'  => ['nullable', 'string', 'max:300'],
            'image_url' => ['required', 'url', 'max:500'],
            'link_url'  => ['nullable', 'url', 'max:500'],
            'placement' => ['required', 'in:hero,promo,strip'],
            'position'  => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ]);

        $banner = Banner::create([
            ...$data,
            'position' => $data['position'] ?? 0,
            'is_active' => $data['is_active'] ?? true,
        ]);

        return response()->json(['message' => 'Banner created.', 'data' => $banner], 201);
    }

    public function updateBanner(Request $request, Banner $banner)
    {
        $this->guard($request);

        $data = $request->validate([
            'title'     => ['sometimes', 'string', 'max:150'],
            'subtitle'  => ['nullable', 'string', 'max:300'],
            'image_url' => ['sometimes', 'url', 'max:500'],
            'link_url'  => ['nullable', 'url', 'max:500'],
            'placement' => ['sometimes', 'in:hero,promo,strip'],
            'position'  => ['sometimes', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $banner->update($data);

        return response()->json(['message' => 'Banner updated.', 'data' => $banner->fresh()]);
    }

    public function destroyBanner(Banner $banner)
    {
        $this->guard(request());

        $banner->delete();

        return response()->json(['message' => 'Banner deleted.']);
    }

    // ----- Notifications -----

    /** POST /api/admin/notifications — broadcast to one user or everyone. */
    public function broadcastNotification(Request $request)
    {
        $this->guard($request);

        $data = $request->validate([
            'user_id'   => ['nullable', 'exists:users,id'],
            'type'      => ['required', 'in:generic,order_status,return_update,payout,wallet,promo'],
            'title'     => ['required', 'string', 'max:150'],
            'body'      => ['nullable', 'string', 'max:1000'],
            'link_url'  => ['nullable', 'string', 'max:500'],
        ]);

        $query = ! empty($data['user_id'])
            ? \App\Models\User::where('id', $data['user_id'])
            : \App\Models\User::where('is_active', true);

        $count = 0;
        $query->chunkById(200, function ($users) use ($data, &$count) {
            foreach ($users as $user) {
                SiteNotification::notify($user->id, $data['type'], $data['title'], $data['body'] ?? null, $data['link_url'] ?? null);
                $count++;
            }
        });

        return response()->json(['message' => "Notification sent to {$count} user(s).", 'data' => ['recipients' => $count]], 201);
    }

    // ----- Public notification endpoints (customer) -----

    /** GET /api/notifications — current user's notifications. */
    public function myNotifications(Request $request)
    {
        $list = SiteNotification::where('user_id', $request->user()->id)
            ->latest('created_at')
            ->paginate((int) $request->query('per_page', 20));

        return response()->json([
            'data' => $list->items(),
            'meta' => [
                'current_page' => $list->currentPage(),
                'last_page' => $list->lastPage(),
                'total' => $list->total(),
                'unread' => SiteNotification::where('user_id', $request->user()->id)->whereNull('read_at')->count(),
            ],
        ]);
    }

    /** POST /api/notifications/{notification}/read */
    public function markRead(Request $request, SiteNotification $notification)
    {
        abort_unless($notification->user_id === $request->user()->id, 403);

        $notification->update(['read_at' => now()]);

        return response()->json(['message' => 'Marked as read.', 'data' => $notification->fresh()]);
    }

    /** POST /api/notifications/read-all */
    public function markAllRead(Request $request)
    {
        SiteNotification::where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['message' => 'All notifications marked as read.']);
    }
}
