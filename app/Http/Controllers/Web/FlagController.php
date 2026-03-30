<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Contracts\Services\FlagServiceInterface;
use App\Http\Requests\Flag\CreateFlagRequest;
use App\Http\Requests\Flag\UpdateFlagRequest;
use App\Models\Flag;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class FlagController extends Controller
{
    public function __construct(
        private readonly FlagServiceInterface $flagService,
    ) {}

    /**
     * Display a listing of flags.
     */
    public function index(): View
    {
        $tenantId = Auth::user()->tenant_id;
        $flags = $this->flagService->getAll($tenantId);

        return view('flags.index', [
            'flags' => $flags,
        ]);
    }

    /**
     * Show form for creating a new flag.
     */
    public function create(): View
    {
        return view('flags.create');
    }

    /**
     * Store a newly created flag.
     */
    public function store(CreateFlagRequest $request): RedirectResponse
    {
        $tenantId = Auth::user()->tenant_id;
        $data = $request->validated();

        $flag = $this->flagService->create($tenantId, $data);

        return redirect()
            ->route('flags.show', $flag)
            ->with('success', "Flag '{$flag->key}' created successfully.");
    }

    /**
     * Display the specified flag.
     */
    public function show(Flag $flag): View
    {
        $flag->load('targetingRules.group');

        return view('flags.show', [
            'flag' => $flag,
        ]);
    }

    /**
     * Show form for editing the specified flag.
     */
    public function edit(Flag $flag): View
    {
        return view('flags.edit', [
            'flag' => $flag,
        ]);
    }

    /**
     * Update the specified flag.
     */
    public function update(UpdateFlagRequest $request, Flag $flag): RedirectResponse
    {
        $data = $request->validated();

        $this->flagService->update($flag->id, $data);

        return redirect()
            ->route('flags.show', $flag)
            ->with('success', "Flag '{$flag->key}' updated successfully.");
    }

    /**
     * Toggle the flag's enabled status.
     */
    public function toggle(Flag $flag): RedirectResponse|JsonResponse
    {
        $this->flagService->toggle($flag->id);
        $flag->refresh();

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'is_enabled' => $flag->is_enabled,
                'message' => $flag->is_enabled ? 'Flag enabled' : 'Flag disabled',
            ]);
        }

        $message = $flag->is_enabled ? 'Flag enabled' : 'Flag disabled';
        return redirect()
            ->back()
            ->with('success', $message);
    }

    /**
     * Remove the specified flag.
     */
    public function destroy(Flag $flag): RedirectResponse
    {
        $key = $flag->key;
        $this->flagService->delete($flag->id);

        return redirect()
            ->route('flags.index')
            ->with('success', "Flag '{$key}' deleted successfully.");
    }
}
