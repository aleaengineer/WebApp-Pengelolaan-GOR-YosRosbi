<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl leading-tight" style="font-family:Poppins"><span class="text-white">Booking</span> <span class="text-primary-100">Lapangan</span></h2>
    </x-slot>
    <div class="py-6 sm:py-8 bg-gray-50 min-h-screen">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <button onclick="if(history.length>1){history.back()}else{window.location.href='{{ route('home') }}'}" class="mb-4 inline-flex items-center gap-2 text-sm font-semibold text-gray-700 bg-white border border-gray-200 px-4 py-2.5 rounded-full shadow-sm hover:bg-gray-50 hover:text-primary-600 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Kembali
            </button>
            <div class="card !p-4 sm:!p-6">
                <div class="bg-primary-50 border border-primary-200 rounded-xl p-3 mb-6 text-sm text-primary-800">
                    🧹 <strong>Jeda 30 menit otomatis</strong> tiap transisi booking (tidak ditagih). Contoh: 08:00-09:00 → jeda 09:00-09:30 → baru bisa 09:30. Sistem akan sarankan slot jika bentrok. Jeda tidak berlaku jika booking dibatalkan.
                </div>

                @if($errors->any())
                    <div class="bg-red-50 border border-red-200 text-red-700 p-3 rounded-xl mb-4 text-sm">
                        <ul class="list-disc pl-5">
                            @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('booking.store') }}" id="bookingForm" class="space-y-4">
                    @csrf
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-semibold text-gray-700">Tanggal</label>
                            <input type="date" name="tanggal" id="tanggal" value="{{ request('tanggal', $tanggal) }}" class="mt-1 block w-full border-gray-300 rounded-lg focus:border-primary-500 focus:ring-primary-500" required>
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-gray-700">Tipe Sewa</label>
                            <select name="tipe_sewa" id="tipe_sewa" class="mt-1 block w-full border-gray-300 rounded-lg focus:border-primary-500 focus:ring-primary-500">
                                <option value="per_jam" {{ request('tipe')=='harian' ? '' : 'selected' }}>Per Jam (08:00-00:00)</option>
                                <option value="harian" {{ request('tipe')=='harian' ? 'selected' : '' }}>Harian Event (Full Day)</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-semibold text-gray-700">Jam Mulai</label>
                            <input type="time" name="jam_mulai" id="jam_mulai" value="{{ request('jam_mulai','08:00') }}" step="1800" class="mt-1 block w-full border-gray-300 rounded-lg focus:border-primary-500 focus:ring-primary-500" required>
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-gray-700">Jam Selesai</label>
                            <input type="time" name="jam_selesai" id="jam_selesai" value="{{ request('jam_selesai','09:00') }}" step="1800" class="mt-1 block w-full border-gray-300 rounded-lg focus:border-primary-500 focus:ring-primary-500" required>
                        </div>
                    </div>

                    <div id="slotRekomendasi" class="flex flex-wrap gap-2 justify-center sm:justify-start">
                        @foreach($fixedSlots as $s)
                            <button type="button" onclick="document.getElementById('jam_mulai').value='{{ $s['start'] }}';document.getElementById('jam_selesai').value='{{ $s['end'] }}';checkAvail()" class="px-3.5 py-2 bg-white border border-primary-200 rounded-full text-xs font-semibold text-primary-700 hover:bg-primary-600 hover:text-white shadow-sm"> {{ $s['start'] }}-{{ $s['end'] }}</button>
                        @endforeach
                    </div>
                    <div id="availResult" class="text-sm hidden p-3 rounded-xl"></div>

                    <div>
                        <label class="text-sm font-semibold text-gray-700">Jenis Kegiatan</label>
                        <select name="jenis_kegiatan" class="mt-1 block w-full border-gray-300 rounded-lg focus:border-primary-500 focus:ring-primary-500" required>
                            <option value="badminton">Badminton</option>
                            <option value="voly">Voly</option>
                            <option value="basket">Basket</option>
                            <option value="event_lain">Event Lain</option>
                        </select>
                    </div>

                    <!-- Kupon Promo Percent - hanya per_jam -->
                    <div class="border-2 border-dashed border-primary-200 bg-gradient-to-br from-primary-50 to-white rounded-2xl p-4">
                        <label class="text-sm font-bold text-gray-900 flex items-center gap-2">🎟️ Kode Promo <span class="text-xs font-normal text-gray-500">(opsional, hanya per jam, 1x/user)</span></label>
                        <div class="mt-2 flex gap-2">
                            <input type="text" name="coupon_code" id="coupon_code" value="{{ old('coupon_code') }}" placeholder="PROMO10" class="flex-1 border-gray-300 rounded-xl focus:border-primary-500 focus:ring-primary-500 uppercase text-sm font-mono tracking-widest" maxlength="20">
                            <button type="button" onclick="checkCoupon()" class="px-5 py-2.5 bg-primary-600 text-white rounded-xl text-sm font-bold hover:bg-primary-700 whitespace-nowrap">Cek</button>
                            <button type="button" onclick="document.getElementById('coupon_code').value='';document.getElementById('couponResult').classList.add('hidden');updateHargaPreview()" class="px-4 py-2.5 border border-gray-200 bg-white rounded-xl text-sm font-semibold hover:bg-gray-50">Hapus</button>
                        </div>
                        <div id="couponResult" class="hidden mt-3 text-sm p-3 rounded-xl"></div>
                        <p class="mt-2 text-xs text-gray-500">Hanya <strong>percent</strong>, tidak bisa bareng kuota gratis member. Contoh: <code class="bg-white px-1.5 py-0.5 rounded border">YOS10</code> 10%</p>
                    </div>

                    <div>
                        <label class="text-sm font-semibold text-gray-700">Metode Pembayaran</label>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-2">
                            <label class="border rounded-xl p-3 flex flex-col items-center cursor-pointer has-[:checked]:border-primary-600 has-[:checked]:bg-primary-50">
                                <input type="radio" name="metode" value="transfer" checked class="sr-only">
                                <span class="text-lg">🏦</span><span class="text-xs font-semibold">Transfer</span><span class="text-xs text-gray-500">Upload bukti</span>
                            </label>
                            <label class="border rounded-xl p-3 flex flex-col items-center cursor-pointer has-[:checked]:border-primary-600 has-[:checked]:bg-primary-50">
                                <input type="radio" name="metode" value="midtrans" class="sr-only">
                                <span class="text-lg">📱</span><span class="text-xs font-semibold">QRIS/Midtrans</span><span class="text-xs text-gray-500">Otomatis</span>
                            </label>
                            <label class="border rounded-xl p-3 flex flex-col items-center cursor-pointer has-[:checked]:border-primary-600 has-[:checked]:bg-primary-50">
                                <input type="radio" name="metode" value="cash" class="sr-only">
                                <span class="text-lg">💵</span><span class="text-xs font-semibold">Cash</span><span class="text-xs text-gray-500">Di tempat</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="text-sm font-semibold text-gray-700">Catatan (opsional)</label>
                        <textarea name="catatan" rows="2" class="mt-1 block w-full border-gray-300 rounded-lg focus:border-primary-500 focus:ring-primary-500" placeholder="Kebutuhan khusus..."></textarea>
                    </div>

                    <div class="bg-gray-50 rounded-xl p-4 text-sm space-y-1" id="hargaPreview">
                        <div>Harga Per Jam: <strong class="text-primary-600">Rp {{ number_format($hargaPerJam->harga ?? 50000,0,',','.') }}</strong> • Harian: <strong class="text-gray-900">Rp {{ number_format($hargaHarian->harga ?? 1500000,0,',','.') }}</strong></div>
                        <div id="diskonPreview" class="hidden text-green-700 font-semibold"></div>
                        <div id="totalPreview" class="hidden text-gray-900 font-bold text-base"></div>
                        <div class="text-xs text-gray-500">Hanya percent, tidak stack kuota gratis. Total final dihitung server.</div>
                    </div>

                    <button type="submit" class="w-full btn-primary">Booking Sekarang</button>
                </form>
            </div>
        </div>
    </div>
    <script>
        function getHargaPerJam(){ return {{ $hargaPerJam->harga ?? 50000 }}; }
        function getDurasi(){
            const jm = document.getElementById('jam_mulai').value, js = document.getElementById('jam_selesai').value;
            if(!jm||!js) return 1;
            const [h1,m1]=jm.split(':').map(Number), [h2,m2]=js.split(':').map(Number);
            let d = (h2*60+m2)-(h1*60+m1); if(d<=0) d+=24*60; return Math.ceil(d/60);
        }
        function updateHargaPreview(discount=0){
            const tipe=document.getElementById('tipe_sewa').value;
            const dur=getDurasi();
            const base = tipe==='harian' ? {{ $hargaHarian->harga ?? 1500000 }} : getHargaPerJam()*dur;
            const disEl=document.getElementById('diskonPreview'), totEl=document.getElementById('totalPreview');
            if(discount>0){
                disEl.classList.remove('hidden'); totEl.classList.remove('hidden');
                disEl.textContent = `Diskon kupon: -Rp${discount.toLocaleString('id-ID')}`;
                totEl.textContent = `Total bayar: Rp${(base-discount).toLocaleString('id-ID')}`;
            } else {
                disEl.classList.add('hidden'); totEl.classList.add('hidden');
            }
        }
        async function checkCoupon(){
            const code=document.getElementById('coupon_code').value.trim();
            const el=document.getElementById('couponResult');
            if(!code){ el.classList.add('hidden'); updateHargaPreview(0); return; }
            const tipe=document.getElementById('tipe_sewa').value;
            const jm=document.getElementById('jam_mulai').value, js=document.getElementById('jam_selesai').value;
            const dur=getDurasi();
            const base = tipe==='harian' ? {{ $hargaHarian->harga ?? 1500000 }} : getHargaPerJam()*dur;
            try{
                const r=await fetch(`/api/check-coupon?code=${encodeURIComponent(code)}&total_harga=${base}&tipe_sewa=${tipe}`);
                const j=await r.json();
                el.classList.remove('hidden');
                if(j.valid){
                    el.className='mt-3 text-sm p-3 rounded-xl bg-green-50 border border-green-200 text-green-700';
                    el.textContent='✓ '+j.message+' → hemat Rp'+(j.discount||0).toLocaleString('id-ID');
                    updateHargaPreview(j.discount||0);
                } else {
                    el.className='mt-3 text-sm p-3 rounded-xl bg-red-50 border border-red-200 text-red-700';
                    el.textContent='✗ '+j.message;
                    updateHargaPreview(0);
                }
            }catch(e){ el.classList.add('hidden'); }
        }
        async function checkAvail(){
            const tanggal = document.getElementById('tanggal').value;
            const jam_mulai = document.getElementById('jam_mulai').value;
            const jam_selesai = document.getElementById('jam_selesai').value;
            const tipe = document.getElementById('tipe_sewa').value;
            if(!tanggal||!jam_mulai||!jam_selesai) return;
            const resEl = document.getElementById('availResult');
            try {
                const r = await fetch(`/api/check-availability?tanggal=${tanggal}&jam_mulai=${jam_mulai}&jam_selesai=${jam_selesai}&tipe_sewa=${tipe}`);
                const j = await r.json();
                resEl.classList.remove('hidden');
                if(j.available){
                    resEl.className = 'text-sm p-3 rounded-xl bg-green-50 border border-green-200 text-green-700';
                    resEl.innerHTML = '✓ Slot tersedia! Jeda 30 menit setelah '+jam_selesai+' otomatis terblokir (tidak ditagih).';
                } else {
                    resEl.className = 'text-sm p-3 rounded-xl bg-red-50 border border-red-200 text-red-700';
                    let sug = j.suggestion ? ` <button type="button" onclick="document.getElementById('jam_mulai').value='${j.suggestion}';let e='${j.suggestion}'.split(':');let h=parseInt(e[0])+1;document.getElementById('jam_selesai').value=(h<10?'0'+h:h)+':'+e[1];checkAvail()" class="underline font-bold">Pakai ${j.suggestion}</button>` : '';
                    resEl.innerHTML = '✗ '+j.reason + sug;
                }
            } catch(e){ resEl.classList.add('hidden');}
            updateHargaPreview(0);
            document.getElementById('couponResult').classList.add('hidden');
        }
        document.getElementById('coupon_code')?.addEventListener('change', checkCoupon);
        document.getElementById('tanggal')?.addEventListener('change', checkAvail);
        document.getElementById('jam_mulai')?.addEventListener('change', checkAvail);
        document.getElementById('jam_selesai')?.addEventListener('change', checkAvail);
        document.getElementById('tipe_sewa')?.addEventListener('change', checkAvail);
    </script>
</x-app-layout>
