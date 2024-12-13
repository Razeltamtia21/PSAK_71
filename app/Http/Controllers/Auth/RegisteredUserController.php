<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\model_pt; // Import model_pt
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // Validasi input
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nama_pt' => ['required', 'string'],
            'alamat_pt' => ['required', 'string'],
            'company_type' => ['required', 'string'],
            'nomor_wa' => ['required', 'string', 'regex:/^[0-9\+]{10,15}$/'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'nama_pt.required' => 'Nama perusahaan wajib diisi.',
            'email.unique' => 'Email sudah digunakan.',
        ]);

        // Hilangkan spasi ekstra pada nama_pt
        $nama_pt = trim($request->nama_pt);

        // Cek apakah nama_pt sudah ada di tabel tbl_pt
        //  $pt = model_pt::where('nama_pt', $nama_pt)->first();
        $pt = model_pt::where('nama_pt', "pt001")->first();


        if (!$pt) {
            // Ambil ID terakhir dari tabel
            $lastId = model_pt::max('id_pt'); // Contoh: 'pt011'

            // Ekstrak angka dari ID terakhir (contoh: dari 'pt011' menjadi 11)
            $lastNumber = $lastId ? (int) substr($lastId, 2) : 0;

            // Tambahkan 1 untuk membuat ID baru
            $newId = 'pt' . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);

            // Simpan entri baru dengan ID baru
            $pt = model_pt::create([
                'id_pt' => $newId, // Tetapkan ID baru secara manual
                'nama_pt' => $nama_pt,
                'alamat_pt' => $request->alamat_pt,
                'company_type' => $request->company_type,
            ]);
        }

        // Debug untuk memastikan id_pt ada dan benar
        // dd($pt->id_pt);

        // Buat pengguna baru dan simpan id_pt dari tabel model_pt ke dalam tabel tbl_users
        $user = User::create([
            'name' => $request->name,
            'nama_pt' => $pt->nama_pt,
            'alamat_pt' => $request->alamat_pt,
            'company_type' => $request->company_type,
            'nomor_wa' => $request->nomor_wa,
            'email' => $request->email,
            //'role' => 'admin', // Set peran (role) sebagai admin secara default
            'role' => 'user',
            'is_activated' => 'false', // Status aktivasi langsung 'aktif'
            'password' => Hash::make($request->password),
            //'id_pt' => $pt->id_pt, // Menyimpan id_pt yang diambil dari model_pt
            'id_pt' => 'pt001',
        ]);

        // Fire Registered event
        event(new Registered($user));

        // Redirect ke halaman login dengan pesan sukses
        return redirect()->route('login')->with('status', 'Terima kasih atas kesediaan Anda untuk registrasi. Kami akan memberikan akses kepada Anda paling lambat 2x24 jam.');
    }
}
