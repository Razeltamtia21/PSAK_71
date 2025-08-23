<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\pt; // Import pt
use Illuminate\Support\Facades\DB;
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

        $user = DB::transaction(function () use ($request) {
            // Hilangkan spasi ekstra pada nama_pt
            $nama_pt = trim($request->nama_pt);
            // Cek apakah nama_pt sudah ada di tabel tbl_pt
            $pt = pt::where('nama_pt', $nama_pt)->first();

            if (!$pt) {
                // Simpan entri baru dengan ID baru
                $pt = pt::create([
                    'nama_pt' => $nama_pt,
                    'alamat_pt' => $request->alamat_pt,
                    'company_type' => $request->company_type,
                ]);
            };

            return User::create([
                'name' => $request->name,
                'nama_pt' => $pt->nama_pt,
                'alamat_pt' => $pt->alamat_pt,
                'company_type' => $pt->company_type,
                'nomor_wa' => $request->nomor_wa,
                'email' => $request->email,
                //'role' => 'admin', // Set peran (role) sebagai admin secara default
                'role' => 'user',
                'is_activated' => 'false', // Status aktivasi langsung 'aktif'
                'password' => Hash::make($request->password),
                //'id_pt' => $pt->id_pt, // Menyimpan id_pt yang diambil dari model_pt
                'id_pt' => $pt->id_pt,
            ]);
        });

        // Fire Registered event
        event(new Registered($user));

        // Redirect ke halaman login dengan pesan sukses
        return redirect()->route('login')->with('status', 'Terima kasih atas kesediaan Anda untuk registrasi. Kami akan memberikan akses kepada Anda paling lambat 2x24 jam.');
    }
}
