<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Contracts\Services\TargetingServiceInterface;
use App\Contracts\Services\GroupServiceInterface;
use App\Models\Flag;
use App\Models\Group;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TargetingController extends Controller
{
    public function __construct(
        private readonly TargetingServiceInterface $targetingService,
        private readonly GroupServiceInterface $groupService,
    ) {}

    /**
     * Show targeting management page for a flag.
     */
    public function manage(Flag $flag): View
    {
        $tenantId = Auth::user()->tenant_id;
        
        $flag->load('targetingRules.group');
        $allGroups = $this->groupService->getAll($tenantId);
        
        $assignedGroupIds = $flag->targetingRules->pluck('group_id')->toArray();
        $availableGroups = $allGroups->filter(fn ($g) => !in_array($g->id, $assignedGroupIds));

        return view('targeting.manage', [
            'flag' => $flag,
            'assignedGroups' => $flag->targetingRules->map(fn ($r) => $r->group),
            'availableGroups' => $availableGroups,
        ]);
    }

    /**
     * Add a group to flag targeting.
     */
    public function store(Request $request, Flag $flag): RedirectResponse
    {
        $validated = $request->validate([
            'group_ids' => 'required|array|min:1',
            'group_ids.*' => 'exists:groups,id',
        ]);

        $this->targetingService->createRules($flag->id, $validated['group_ids']);

        return redirect()
            ->route('flags.targeting.manage', $flag)
            ->with('success', 'Targeting rules added successfully.');
    }

    /**
     * Remove a group from flag targeting.
     */
    public function destroy(Flag $flag, Group $group): RedirectResponse
    {
        $this->targetingService->removeRule($flag->id, $group->id);

        return redirect()
            ->route('flags.targeting.manage', $flag)
            ->with('success', "Group '{$group->identifier}' removed from targeting.");
    }

    /**
     * Replace all targeting rules.
     */
    public function replace(Request $request, Flag $flag): RedirectResponse
    {
        $validated = $request->validate([
            'group_ids' => 'nullable|array',
            'group_ids.*' => 'exists:groups,id',
        ]);

        $this->targetingService->replaceRules($flag->id, $validated['group_ids'] ?? []);

        return redirect()
            ->route('flags.targeting.manage', $flag)
            ->with('success', 'Targeting rules updated successfully.');
    }
}
