<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BlokirJadwal;
use App\Models\HargaSewa;
use App\Models\PaketMember;
use App\Models\Setting;
use Illuminate\Http\Request;

class AdminBookingController extends Controller
{
    public function index(Request $request)
    {
        $query = Booking::with(['user','lapangan','payment'])->latest();
        if ($request->status) $query->where('status', $request->status);
        if ($request->tanggal) $query->whereDate('tanggal', $request->tanggal);
        $bookings = $query->paginate(15);
        return view('admin.bookings.index', compact('bookings'));
    }

    public function show(Booking $booking)
    {
        $booking->load(['user','lapangan','payment']);
        return view('admin.bookings.show', compact('booking'));
    }

    public function updateStatus(Request $request, Booking $booking)
    {
        $request->validate(['status' => 'required|in:paid,confirmed,completed,cancelled']);
        $booking->update(['status' => $request->status]);
        if ($request->status === 'paid' || $request->status === 'confirmed') {
            $booking->payment()->update(['status' => 'paid', 'paid_at' => now()]);
        }
        if ($request->status === 'cancelled') {
            $booking->payment()->update(['status' => 'failed']);
        }
        return back()->with('success','Status booking diupdate ke '.$request->status);
    }

    public function blokirIndex()
    {
        $blokirs = BlokirJadwal::latest()->paginate(15);
        return view('admin.blokir.index', compact('blokirs'));
    }

    public function blokirStore(Request $request)
    {
        $request->validate([
            'lapangan_id' => 'required|exists:lapangans,id',
            'tanggal' => 'required|date',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required',
            'alasan' => 'required|string|max:255'
        ]);
        BlokirJadwal::create([
            'lapangan_id' => $request->lapangan_id,
            'tanggal' => $request->tanggal,
            'jam_mulai' => $request->jam_mulai,
            'jam_selesai' => $request->jam_selesai,
            'alasan' => $request->alasan,
            'created_by' => auth()->id()
        ]);
        return back()->with('success','Jadwal blokir berhasil ditambahkan');
    }

    public function blokirDestroy(BlokirJadwal $blokir)
    {
        $blokir->delete();
        return back()->with('success','Blokir dihapus');
    }

    public function settings()
    {
        $settings = Setting::all()->pluck('value','key');
        $pakets = PaketMember::all();
        return view('admin.settings', compact('settings','pakets'));
    }

    public function settingsUpdate(Request $request)
    {
        $keys = ['jam_operasional_mulai','jam_operasional_selesai','buffer_menit','buffer_harian_menit','harga_per_jam','harga_harian','rekening','qris','kontak_wa','nama_gor'];
        foreach ($keys as $k) {
            if ($request->has($k)) Setting::set($k, $request->input($k));
        }
        if ($request->has('harga_per_jam')) {
            HargaSewa::where('tipe','per_jam')->update(['harga' => (int) $request->input('harga_per_jam')]);
        }
        if ($request->has('harga_harian')) {
            HargaSewa::where('tipe','harian')->update(['harga' => (int) $request->input('harga_harian')]);
        }
        foreach ($request->all() as $key => $value) {
            if (str_starts_with($key, 'paket_harga_')) {
                $id = (int) substr($key, strlen('paket_harga_'));
                $paket = PaketMember::find($id);
                if ($paket) $paket->update(['harga' => (int) $value]);
            }
        }
        return back()->with('success','Pengaturan disimpan');
    }

    public function laporan(Request $request)
    {
        $query = Booking::with('payment')->whereIn('status', ['paid','confirmed','completed']);
        if ($request->dari) $query->whereDate('tanggal','>=',$request->dari);
        if ($request->sampai) $query->whereDate('tanggal','<=',$request->sampai);
        if ($request->jenis) $query->where('jenis_kegiatan',$request->jenis);
        $bookings = $query->get();
        $total = $bookings->sum('total_harga');
        $totalJam = $bookings->sum('durasi_jam');
        return view('admin.laporan', compact('bookings','total','totalJam'));
    }
}
