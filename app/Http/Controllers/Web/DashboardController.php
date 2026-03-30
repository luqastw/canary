<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Contracts\Services\FlagServiceInterface;
use App\Contracts\Services\GroupServiceInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct(
        private readonly FlagServiceInterface $flagService,
        private readonly GroupServiceInterface $groupService,
    ) {}

    public function index(): View
    {
        $tenantId = Auth::user()->tenant_id;

        $flags = $this->flagService->getAll($tenantId);
        $groups = $this->groupService->getAll($tenantId);

        $totalFlags = $flags->count();
        $activeFlags = $flags->where('is_enabled', true)->count();
        $flagsWithTargeting = $flags->filter(fn ($flag) => $flag->hasTargeting())->count();
        $totalGroups = $groups->count();

        $recentFlags = $flags->sortByDesc('created_at')->take(5);

        return view('dashboard.index', [
            'totalFlags' => $totalFlags,
            'activeFlags' => $activeFlags,
            'flagsWithTargeting' => $flagsWithTargeting,
            'totalGroups' => $totalGroups,
            'recentFlags' => $recentFlags,
        ]);
    }
}
