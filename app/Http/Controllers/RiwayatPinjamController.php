<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use App\Models\User;
use App\Models\Profile;
use App\Models\Peminjaman;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use RealRashid\SweetAlert\Facades\Alert;

class RiwayatPinjamController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $iduser = Auth::id();
        $profile = Profile::where('users_id', $iduser)->first();
        $peminjam = Peminjaman::with(['user', 'buku'])->orderBy('updated_at', 'desc')->get();
        $pinjamanUser = Peminjaman::where('users_id', $iduser)->get();
        return view('peminjaman.tampil', ['profile' => $profile, 'peminjam' => $peminjam, 'pinjamanUser' => $pinjamanUser]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $iduser = Auth::id();
        $profile = Profile::where('users_id', $iduser)->first();
        $buku = Buku::where('status', 'Tersedia')->get();
        $user = User::all();

        // Jika admin, tampilkan semua profil pengguna selain admin
        if (Auth::user()->isAdmin == 1) {
            $peminjam = Profile::where('users_id', '!=', Auth::id())->get();
        } else {
            $peminjam = $profile;
        }

        return view('peminjaman.tambah', ['profile' => $profile, 'users' => $user, 'buku' => $buku, 'peminjam' => $peminjam]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Validasi input
        $request->validate(
            [
                'users_id' => 'required',
                'buku_id' => 'required'
            ],
            [
                'users_id.required' => 'Harap Masukan Nama Peminjam',
                'buku_id.required' => 'Masukan Buku yang akan dipinjam'
            ]
        );

        // Menentukan tanggal pinjam dan tanggal wajib kembali
        $request['tanggal_pinjam'] = Carbon::now()->toDateString();
        $request['tanggal_wajib_kembali'] = Carbon::now()->addDay(7)->toDateString();

        // Ambil data buku yang dipilih
        $buku = Buku::findOrFail($request->buku_id);

        // Cek apakah pengguna sudah mencapai batas peminjaman (3 buku yang belum dikembalikan)
        $count = Peminjaman::where('users_id', $request->users_id)->whereNull('tanggal_pengembalian')->count();

        if ($count >= 3) {
            Alert::warning('Gagal', 'User telah mencapai limit untuk meminjam buku');
            return redirect('/peminjaman/create');
        } else {
            try {
                DB::beginTransaction();

                // Proses insert tabel peminjaman
                Peminjaman::create($request->all());

                // Proses update status buku menjadi 'dipinjam'
                $buku->status = 'dipinjam';
                $buku->save();

                // Commit transaksi
                DB::commit();

                Alert::success('Berhasil', 'Berhasil Meminjam Buku');
                return redirect('/peminjaman');
            } catch (\Throwable $th) {
                DB::rollback();
                Alert::error('Gagal', 'Terjadi kesalahan saat memproses peminjaman');
                return redirect('/peminjaman/create');
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        // Dapatkan riwayat peminjaman berdasarkan ID
        $peminjaman = Peminjaman::with(['user', 'buku'])->findOrFail($id);
        return view('peminjaman.show', ['peminjaman' => $peminjaman]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        // Ambil data peminjaman untuk diedit
        $peminjaman = Peminjaman::findOrFail($id);
        $buku = Buku::all();
        return view('peminjaman.edit', ['peminjaman' => $peminjaman, 'buku' => $buku]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        // Validasi data yang akan diupdate
        $request->validate(
            [
                'users_id' => 'required',
                'buku_id' => 'required'
            ],
            [
                'users_id.required' => 'Harap Masukan Nama Peminjam',
                'buku_id.required' => 'Masukan Buku yang akan dipinjam'
            ]
        );

        // Update data peminjaman
        $peminjaman = Peminjaman::findOrFail($id);
        $peminjaman->update($request->all());

        // Jika status buku berubah, update status buku
        if ($peminjaman->isDirty('buku_id')) {
            $buku = Buku::findOrFail($peminjaman->buku_id);
            $buku->status = 'dipinjam';
            $buku->save();
        }

        Alert::success('Berhasil', 'Berhasil Memperbarui Peminjaman');
        return redirect('/peminjaman');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        // Hapus data peminjaman
        $peminjaman = Peminjaman::findOrFail($id);
        $buku = Buku::findOrFail($peminjaman->buku_id);

        // Kembalikan status buku menjadi 'Tersedia'
        $buku->status = 'Tersedia';
        $buku->save();

        $peminjaman->delete();

        Alert::success('Berhasil', 'Berhasil Menghapus Peminjaman');
        return redirect('/peminjaman');
    }
}
