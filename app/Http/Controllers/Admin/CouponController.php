<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function index()
    {
        $coupons = Coupon::latest()->paginate(15);
        return view('admin.coupons.index', compact('coupons'));
    }

    public function create()
    {
        return view('admin.coupons.form');
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:20|unique:coupons,code',
            'name' => 'required|string|max:100',
            'value' => 'required|integer|min:1|max:100',
            'max_discount' => 'nullable|integer|min:0',
            'min_amount' => 'nullable|integer|min:0',
            'quota' => 'nullable|integer|min:1',
            'per_user_limit' => 'required|integer|min:1',
            'expired_at' => 'nullable|date|after:now',
        ]);
        Coupon::create([
            'code' => strtoupper(trim($request->code)),
            'name' => $request->name,
            'type' => 'percent',
            'value' => $request->value,
            'max_discount' => $request->max_discount,
            'min_amount' => $request->min_amount ?? 0,
            'quota' => $request->quota,
            'per_user_limit' => $request->per_user_limit,
            'tipe_sewa' => 'per_jam',
            'is_active' => $request->has('is_active'),
            'expired_at' => $request->expired_at,
            'created_by' => auth()->id(),
        ]);
        return redirect()->route('admin.coupons.index')->with('success','Kupon berhasil dibuat');
    }

    public function edit(Coupon $coupon)
    {
        return view('admin.coupons.form', compact('coupon'));
    }

    public function update(Request $request, Coupon $coupon)
    {
        $request->validate([
            'code' => 'required|string|max:20|unique:coupons,code,'.$coupon->id,
            'name' => 'required|string|max:100',
            'value' => 'required|integer|min:1|max:100',
            'max_discount' => 'nullable|integer|min:0',
            'min_amount' => 'nullable|integer|min:0',
            'quota' => 'nullable|integer|min:1',
            'per_user_limit' => 'required|integer|min:1',
            'expired_at' => 'nullable|date',
        ]);
        $coupon->update([
            'code' => strtoupper(trim($request->code)),
            'name' => $request->name,
            'value' => $request->value,
            'max_discount' => $request->max_discount,
            'min_amount' => $request->min_amount ?? 0,
            'quota' => $request->quota,
            'per_user_limit' => $request->per_user_limit,
            'is_active' => $request->has('is_active'),
            'expired_at' => $request->expired_at,
        ]);
        return redirect()->route('admin.coupons.index')->with('success','Kupon diperbarui');
    }

    public function destroy(Coupon $coupon)
    {
        if ($coupon->used_count > 0) {
            return back()->withErrors(['error' => 'Kupon sudah dipakai, nonaktifkan saja']);
        }
        $coupon->delete();
        return back()->with('success','Kupon dihapus');
    }

    public function toggle(Coupon $coupon)
    {
        $coupon->update(['is_active' => !$coupon->is_active]);
        return back()->with('success','Status kupon diubah');
    }
}
