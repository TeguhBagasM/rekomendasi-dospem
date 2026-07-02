<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\DashboardService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardService $dashboardService
    ) {
    }

    public function index(): View|RedirectResponse
    {
        /** @var User|null $user */
        $user = Auth::user();

        abort_unless($user, 403);

        if ($user->isAdmin()) {
            return view('dashboard', $this->dashboardService->forAdmin());
        }

        if ($user->isDosen()) {
            return view('dashboard', $this->dashboardService->forDosen($user));
        }

        if ($user->isMahasiswa()) {
            return view('dashboard', $this->dashboardService->forMahasiswa($user));
        }

        abort(403);
    }
}