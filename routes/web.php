<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\NasabahController;
use App\Http\Controllers\DepositController;
use App\Http\Controllers\PromosiAgentController;
use App\Http\Controllers\CoaDashboardController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\KomisiReportController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\AgentCommissionReportController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\RoleMiddleware;

// // Guest: hanya yang belum login
// Route::middleware('guest')->group(function () {
//     Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
//     Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
// });


// // Auth: hanya user login
// Route::middleware('auth')->group(function () {
//     Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
//     Route::get('/user/dashboard', [UserController::class, 'dashboard'])->name('user.dashboard');
//     Route::get('/user/komisi', [UserController::class, 'komisi'])->name('user.komisi');
    
//     Route::get('/', function () {
//         // nanti bisa bikin dashboard, untuk sekarang redirect ke agents
//         return redirect()->route('agents.index');
//     })->name('dashboard');

//     Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

//     Route::resource('agents', AgentController::class);
//     Route::resource('nasabah', NasabahController::class);
//     Route::resource('deposits', DepositController::class);


//     // CRUD umum (index dilihat semua role)
//     Route::get('/deposits', [DepositController::class, 'index'])->name('deposits.index');
//     Route::get('/deposits/create', [DepositController::class, 'create'])->name('deposits.create');
//     Route::post('/deposits', [DepositController::class, 'store'])->name('deposits.store');
//     Route::get('/deposits/{deposit}/edit', [DepositController::class, 'edit'])->name('deposits.edit');
//     Route::put('/deposits/{deposit}', [DepositController::class, 'update'])->name('deposits.update');
//     Route::delete('/deposits/{deposit}', [DepositController::class, 'destroy'])->name('deposits.destroy');

//     // approval khusus COA
//     Route::get('/deposits-pending', [DepositController::class, 'pendingForCoa'])->name('deposits.pending');
//     Route::get('/deposits-pending/{deposit}', [DepositController::class, 'showForCoa'])->name('deposits.pending.show');
//     Route::post('/deposits-pending/{deposit}/approve', [DepositController::class, 'approve'])->name('deposits.approve');
//     Route::post('/deposits-pending/{deposit}/reject', [DepositController::class, 'reject'])->name('deposits.reject');

//       // Area COA: approval deposito
//     Route::prefix('coa')->name('coa.')->group(function () {
//         Route::get('/dashboard', [CoaDashboardController::class, 'index'])->name('dashboard');
//          // ===== AGENT (READ ONLY) =====
//         Route::get('agents', [AgentController::class, 'coaIndex'])
//             ->name('agents.index');
//         Route::get('agents/{agent}', [AgentController::class, 'coaShow'])
//             ->name('agents.show');

//         // ===== NASABAH (READ ONLY) =====
//         Route::get('nasabah', [NasabahController::class, 'coaIndex'])
//             ->name('nasabah.index');
//         Route::get('nasabah/{nasabah}', [NasabahController::class, 'coaShow'])
//             ->name('nasabah.show');
            
//         // ===== DEPOSIT (APPROVAL) =====
//         Route::get('deposits', [DepositController::class, 'coaIndex'])
//             ->name('deposits.index');
//         Route::get('deposits/{deposit}', [DepositController::class, 'coaShow'])
//             ->name('deposits.show');
//         Route::post('deposits/{deposit}/approve', [DepositController::class, 'coaApprove'])
//             ->name('deposits.approve');
//         Route::post('deposits/{deposit}/reject', [DepositController::class, 'coaReject'])
//             ->name('deposits.reject');

//             // Data Agent untuk COA
//         Route::get('agents', [AgentController::class, 'coaIndex'])
//             ->name('agents.index');
//         Route::get('agents/{agent}', [AgentController::class, 'coaShow'])
//             ->name('agents.show');
//     });

//      // ADMIN: promosi agent (tanpa prefix admin)
//     Route::get('promosi-agent', [PromosiAgentController::class, 'adminIndex'])
//         ->name('promosi.index');
//     Route::get('promosi-agent/create', [PromosiAgentController::class, 'create'])
//         ->name('promosi.create');
//     Route::post('promosi-agent', [PromosiAgentController::class, 'store'])
//         ->name('promosi.store');
//     Route::get('promosi-agent/{promosi}/edit', [PromosiAgentController::class, 'edit'])
//         ->name('promosi.edit');
//     Route::put('promosi-agent/{promosi}', [PromosiAgentController::class, 'update'])
//         ->name('promosi.update');

//     // COA: approve/reject
//     Route::prefix('coa')->name('coa.')->group(function () {
//         Route::get('promosi-agent', [PromosiAgentController::class, 'coaIndex'])
//             ->name('promosi.index');
//         Route::get('promosi-agent/{promosi}', [PromosiAgentController::class, 'coaShow'])
//             ->name('promosi.show');
//         Route::post('promosi-agent/{promosi}/approve', [PromosiAgentController::class, 'approve'])
//             ->name('promosi.approve');
//         Route::post('promosi-agent/{promosi}/reject', [PromosiAgentController::class, 'reject'])
//             ->name('promosi.reject');
//     });

//     Route::get('/komisi-report', [KomisiReportController::class, 'index'])->name('komisi.report');
//     Route::get('/komisi-report/export', [KomisiReportController::class, 'export'])->name('komisi.report.export');
// });
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::middleware('auth')->group(function () {

    // =========================
    // ROOT (setelah login)
    // =========================
    Route::get('/', function () {
        // arahkan ke dashboard sesuai role
        $role = strtolower(trim((string) auth()->user()->role));

        return match ($role) {
            'superadmin' => redirect()->route('superadmin.dashboard'),
            'admin' => redirect()->route('admin.dashboard'),
            'coa'   => redirect()->route('coa.dashboard'),
            'rm', 'bdp' => redirect()->route('user.dashboard'),
            default => redirect()->route('admin.dashboard'),
        };
    })->name('dashboard');


    // =========================
    // LOGOUT
    // =========================
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::middleware(['auth', 'role:superadmin'])->group(function () {
        Route::get('/superadmin/dashboard', [App\Http\Controllers\SuperAdminDashboardController::class, 'index'])->name('superadmin.dashboard');

        Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserManagementController::class, 'create'])->name('users.create');
        Route::post('/users', [UserManagementController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [UserManagementController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserManagementController::class, 'update'])->name('users.update');
        Route::post('/users/{user}/reset-password', [UserManagementController::class, 'resetPassword'])
            ->name('users.resetPassword');

        // Riwayat Aktifitas (Moved from Admin)
        Route::get('/activity-logs', [App\Http\Controllers\ActivityLogController::class, 'index'])->name('admin.activity_logs');
    });
    // =========================
    // USER (RM/BDP) - read only
    // =========================
    Route::middleware('role:rm,bdp')->group(function () {
        Route::get('/user/dashboard', [UserController::class, 'dashboard'])->name('user.dashboard');
        Route::get('/user/komisi', [UserController::class, 'komisi'])->name('user.komisi');
    });


    // =========================
    // ADMIN AREA
    // =========================
    Route::middleware('role:admin')->group(function () {

        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');

        // CRUD utama admin
        Route::resource('agents', AgentController::class);
        Route::resource('nasabah', NasabahController::class);
        Route::resource('deposits', DepositController::class);

        // Laporan komisi admin
        Route::get('/komisi-report', [KomisiReportController::class, 'index'])->name('komisi.report');
        Route::get('/komisi-report/export', [KomisiReportController::class, 'export'])->name('komisi.report.export');
        Route::post('/komisi-report/pay', [KomisiReportController::class, 'pay'])->name('komisi.report.pay');

        // Laporan Komisi PER AGENT
        Route::get('/agent-commission-report', [AgentCommissionReportController::class, 'index'])->name('agent.komisi.report');
        Route::get('/agent-commission-report/{agent}', [AgentCommissionReportController::class, 'show'])->name('agent.komisi.report.show');

        
        // PROMOSI AGENT (admin)
        Route::get('promosi-agent', [PromosiAgentController::class, 'adminIndex'])->name('promosi.index');
        Route::get('promosi-agent/create', [PromosiAgentController::class, 'create'])->name('promosi.create');
        Route::post('promosi-agent', [PromosiAgentController::class, 'store'])->name('promosi.store');
        Route::get('promosi-agent/{promosi}/edit', [PromosiAgentController::class, 'edit'])->name('promosi.edit');
        Route::put('promosi-agent/{promosi}', [PromosiAgentController::class, 'update'])->name('promosi.update');

    });


    // =========================
    // COA AREA
    // =========================
    Route::middleware('role:coa')->prefix('coa')->name('coa.')->group(function () {

        Route::get('/dashboard', [CoaDashboardController::class, 'index'])->name('dashboard');

        // AGENT (READ ONLY)
        Route::get('agents', [AgentController::class, 'coaIndex'])->name('agents.index');
        Route::get('agents/{agent}', [AgentController::class, 'coaShow'])->name('agents.show');

        // NASABAH (READ ONLY)
        Route::get('nasabah', [NasabahController::class, 'coaIndex'])->name('nasabah.index');
        Route::get('nasabah/{nasabah}', [NasabahController::class, 'coaShow'])->name('nasabah.show');

        // DEPOSIT (APPROVAL)
        Route::get('deposits', [DepositController::class, 'coaIndex'])->name('deposits.index');
        Route::get('deposits/{deposit}', [DepositController::class, 'coaShow'])->name('deposits.show');
        Route::post('deposits/{deposit}/approve', [DepositController::class, 'coaApprove'])->name('deposits.approve');
        Route::post('deposits/{deposit}/reject', [DepositController::class, 'coaReject'])->name('deposits.reject');

        // PROMOSI AGENT (COA approve/reject)
        Route::get('promosi-agent', [PromosiAgentController::class, 'coaIndex'])->name('promosi.index');
        Route::get('promosi-agent/{promosi}', [PromosiAgentController::class, 'coaShow'])->name('promosi.show');
        Route::post('promosi-agent/{promosi}/approve', [PromosiAgentController::class, 'approve'])->name('promosi.approve');
        Route::post('promosi-agent/{promosi}/reject', [PromosiAgentController::class, 'reject'])->name('promosi.reject');
        Route::post('deposits/{deposit}/update-approved', [DepositController::class, 'coaUpdateApproved'])->name('deposits.updateApproved');

    });

});