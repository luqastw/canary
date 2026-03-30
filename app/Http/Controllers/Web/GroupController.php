<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Contracts\Services\GroupServiceInterface;
use App\Http\Requests\Group\CreateGroupRequest;
use App\Http\Requests\Group\UpdateGroupRequest;
use App\Models\Group;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class GroupController extends Controller
{
    public function __construct(
        private readonly GroupServiceInterface $groupService,
    ) {}

    /**
     * Display a listing of groups.
     */
    public function index(): View
    {
        $tenantId = Auth::user()->tenant_id;
        $groups = $this->groupService->getAll($tenantId);

        return view('groups.index', [
            'groups' => $groups,
        ]);
    }

    /**
     * Show form for creating a new group.
     */
    public function create(): View
    {
        return view('groups.create');
    }

    /**
     * Store a newly created group.
     */
    public function store(CreateGroupRequest $request): RedirectResponse
    {
        $tenantId = Auth::user()->tenant_id;
        $data = $request->validated();

        $group = $this->groupService->create($tenantId, $data);

        return redirect()
            ->route('groups.index')
            ->with('success', "Group '{$group->identifier}' created successfully.");
    }

    /**
     * Show form for editing the specified group.
     */
    public function edit(Group $group): View
    {
        return view('groups.edit', [
            'group' => $group,
        ]);
    }

    /**
     * Update the specified group.
     */
    public function update(UpdateGroupRequest $request, Group $group): RedirectResponse
    {
        $data = $request->validated();

        $this->groupService->update($group->id, $data);

        return redirect()
            ->route('groups.index')
            ->with('success', "Group '{$group->identifier}' updated successfully.");
    }

    /**
     * Remove the specified group.
     */
    public function destroy(Group $group): RedirectResponse
    {
        $identifier = $group->identifier;
        $this->groupService->delete($group->id);

        return redirect()
            ->route('groups.index')
            ->with('success', "Group '{$identifier}' deleted successfully.");
    }
}
