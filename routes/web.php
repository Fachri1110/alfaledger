<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\KbtkController;
use App\Http\Controllers\SdController;
use App\Http\Controllers\SmpController;
use App\Http\Controllers\KelolaPembayaranController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Public & Authentication Routes
Route::get('/', function () {
    return redirect()->route('login');
});

Auth::routes(['register' => false]);

// Authenticated Application Routes
Route::middleware('auth')->group(function () {

    // General Home Redirect
    Route::get('/home', [HomeController::class, 'index'])->name('home');

    // Case-Insensitive URL Alias Support
    Route::get('/Admin/{path?}', function ($path = null) {
        return redirect('/admin' . ($path ? '/' . $path : ''));
    })->where('path', '.*');

    Route::get('/Superadmin/{path?}', function ($path = null) {
        return redirect('/superadmin' . ($path ? '/' . $path : ''));
    })->where('path', '.*');

    Route::get('/Stafyayasan/{path?}', function ($path = null) {
        return redirect('/stafyayasan' . ($path ? '/' . $path : ''));
    })->where('path', '.*');

    Route::get('/Ketuayayasan/{path?}', function ($path = null) {
        return redirect('/ketuayayasan' . ($path ? '/' . $path : ''));
    })->where('path', '.*');

    /*
    |--------------------------------------------------------------------------
    | Role: Ketua Yayasan
    | URL Prefix: /ketuayayasan
    | Route Names: ketuayayasan.*
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:Ketua Yayasan')
        ->prefix('ketuayayasan')
        ->as('ketuayayasan.')
        ->group(function () {
            Route::get('/dashboard', [UserController::class, 'dashboard'])->name('dashboard');
            Route::resource('users', UserController::class);

            Route::prefix('kbtk')->as('kbtk.')->group(function () {
                Route::get('/dashboard', [KbtkController::class, 'dashboard'])->name('dashboard');
            });

            Route::prefix('sd')->as('sd.')->group(function () {
                Route::get('/dashboard', [SdController::class, 'dashboard'])->name('dashboard');
                Route::get('/rekap-uang-pangkal', [SdController::class, 'rekapUangPangkal'])->name('rekap-pangkal');
                Route::get('/rekap-spp', [SdController::class, 'rekapSpp'])->name('rekap-spp');
                Route::get('/kelola-uang-pangkal', [KelolaPembayaranController::class, 'indexPangkal'])->name('kelola-pangkal');
                Route::post('/kelola-uang-pangkal', [KelolaPembayaranController::class, 'updatePangkal'])->name('kelola-pangkal.update');
            });

            Route::prefix('smp')->as('smp.')->group(function () {
                Route::get('/dashboard', [SmpController::class, 'dashboard'])->name('dashboard');
            });
        });

    /*
    |--------------------------------------------------------------------------
    | Role: Staf Yayasan
    | URL Prefix: /stafyayasan
    | Route Names: stafyayasan.*
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:Staf Yayasan')
        ->prefix('stafyayasan')
        ->as('stafyayasan.')
        ->group(function () {
            Route::get('/dashboard', [UserController::class, 'dashboard'])->name('dashboard');
            Route::resource('users', UserController::class);

            Route::prefix('kbtk')->as('kbtk.')->group(function () {
                Route::get('/dashboard', [KbtkController::class, 'dashboard'])->name('dashboard');
            });

            Route::prefix('sd')->as('sd.')->group(function () {
                Route::get('/dashboard', [SdController::class, 'dashboard'])->name('dashboard');
                Route::get('/rekap-uang-pangkal', [SdController::class, 'rekapUangPangkal'])->name('rekap-pangkal');
                Route::get('/rekap-spp', [SdController::class, 'rekapSpp'])->name('rekap-spp');
                Route::get('/kelola-uang-pangkal', [KelolaPembayaranController::class, 'indexPangkal'])->name('kelola-pangkal');
                Route::post('/kelola-uang-pangkal', [KelolaPembayaranController::class, 'updatePangkal'])->name('kelola-pangkal.update');
                Route::get('/kelola-spp', [KelolaPembayaranController::class, 'indexSpp'])->name('kelola-spp');
                Route::post('/kelola-spp', [KelolaPembayaranController::class, 'updateSpp'])->name('kelola-spp.update');
            });

            Route::prefix('smp')->as('smp.')->group(function () {
                Route::get('/dashboard', [SmpController::class, 'dashboard'])->name('dashboard');
            });
        });

    /*
    |--------------------------------------------------------------------------
    | Role: Superadmin (Generic Alias for Yayasan)
    | URL Prefix: /superadmin
    | Route Names: superadmin.*
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:Ketua Yayasan|Staf Yayasan')
        ->prefix('superadmin')
        ->as('superadmin.')
        ->group(function () {
            Route::get('/dashboard', [UserController::class, 'dashboard'])->name('dashboard');
            Route::resource('users', UserController::class);

            Route::prefix('kbtk')->as('kbtk.')->group(function () {
                Route::get('/dashboard', [KbtkController::class, 'dashboard'])->name('dashboard');
            });

            Route::prefix('sd')->as('sd.')->group(function () {
                Route::get('/dashboard', [SdController::class, 'dashboard'])->name('dashboard');
                Route::get('/rekap-uang-pangkal', [SdController::class, 'rekapUangPangkal'])->name('rekap-pangkal');
                Route::get('/rekap-spp', [SdController::class, 'rekapSpp'])->name('rekap-spp');
            });

            Route::prefix('smp')->as('smp.')->group(function () {
                Route::get('/dashboard', [SmpController::class, 'dashboard'])->name('dashboard');
            });
        });

    /*
    |--------------------------------------------------------------------------
    | Role: Staf TU / Admin
    | URL Prefix: /admin
    | Route Names: admin.*
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:Staf TU')
        ->prefix('admin')
        ->as('admin.')
        ->group(function () {
            Route::get('/dashboard', function () {
                $user = auth()->user();
                $jenjang = $user->jenjang;
                if ($jenjang) {
                    $folder = strtolower(str_replace('/', '', $jenjang->nama_jenjang));
                    $routeName = "admin.{$folder}.dashboard";
                    if (Route::has($routeName)) {
                        return redirect()->route($routeName);
                    }
                }
                return view('admin.dashboard');
            })->name('dashboard');

            Route::prefix('kbtk')->as('kbtk.')->group(function () {
                Route::get('/dashboard', [KbtkController::class, 'dashboard'])->name('dashboard');
            });

            Route::prefix('sd')->as('sd.')->group(function () {
                Route::get('/dashboard', [SdController::class, 'dashboard'])->name('dashboard');
                Route::get('/rekap-uang-pangkal', [SdController::class, 'rekapUangPangkal'])->name('rekap-pangkal');
                Route::get('/rekap-spp', [SdController::class, 'rekapSpp'])->name('rekap-spp');

                Route::get('/kelola-uang-pangkal', [KelolaPembayaranController::class, 'indexPangkal'])->name('kelola-pangkal');
                Route::post('/kelola-uang-pangkal', [KelolaPembayaranController::class, 'updatePangkal'])->name('kelola-pangkal.update');

                Route::get('/kelola-spp', [KelolaPembayaranController::class, 'indexSpp'])->name('kelola-spp');
                Route::post('/kelola-spp', [KelolaPembayaranController::class, 'updateSpp'])->name('kelola-spp.update');

                Route::prefix('kelas')->as('kelas.')->group(function () {
                    Route::get('/uap', [SdController::class, 'uapKelas'])->name('uap');
                    Route::post('/uap/bayar', [SdController::class, 'storeUapKelas'])->name('uap.bayar');
                    Route::post('/uap/koreksi', [SdController::class, 'koreksiSisaUAP'])->name('uap.koreksi');

                    Route::get('/spp', [SdController::class, 'sppKelas'])->name('spp');
                    Route::post('/spp/bayar', [SdController::class, 'storeSppKelas'])->name('spp.bayar');
                    Route::post('/spp/koreksi', [SdController::class, 'koreksiSppKelas'])->name('spp.koreksi');

                    Route::get('/siswa', [SdController::class, 'kelolaSiswaKelas'])->name('siswa');
                    Route::post('/siswa', [SdController::class, 'storeSiswaKelas'])->name('siswa.store');
                    Route::put('/siswa/{id}', [SdController::class, 'updateSiswaKelas'])->name('siswa.update');
                    Route::delete('/siswa/{id}', [SdController::class, 'destroySiswaKelas'])->name('siswa.destroy');
                    Route::post('/pembayaran/batal', [SdController::class, 'batalPembayaran'])->name('pembayaran.batal');
                    Route::post('/naik-kelas', [SdController::class, 'naikKelas'])->name('naik-kelas');
                });
            });

            Route::prefix('smp')->as('smp.')->group(function () {
                Route::get('/dashboard', [SmpController::class, 'dashboard'])->name('dashboard');
            });
        });
});
