<?php

namespace App\Http\Controllers\Api\V1;

use App\Contracts\Services\FlagServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Flag\CreateFlagRequest;
use App\Http\Requests\Flag\UpdateFlagRequest;
use App\Http\Resources\FlagCollection;
use App\Http\Resources\FlagResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FlagController extends Controller
{
    public function __construct(
        private FlagServiceInterface $flagService
    ) {}

    /**
     * Display a listing of flags.
     */
    public function index(Request $request): FlagCollection
    {
        $filters = $request->only(['is_enabled', 'search']);
        $flags = $this->flagService->getAll($request->user()->tenant_id, $filters);

        return new FlagCollection($flags);
    }

    /**
     * Store a newly created flag.
     */
    public function store(CreateFlagRequest $request): JsonResponse
    {
        $flag = $this->flagService->create(
            $request->user()->tenant_id,
            $request->validated()
        );

        return response()->json([
            'message' => 'Flag created successfully.',
            'data' => new FlagResource($flag),
        ], 201);
    }

    /**
     * Display the specified flag.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $flag = $this->flagService->getById($id);

        if (!$flag || $flag->tenant_id !== $request->user()->tenant_id) {
            throw new NotFoundHttpException('Flag not found.');
        }

        $flag->load('targetingRules.group');

        return response()->json([
            'data' => new FlagResource($flag),
        ]);
    }

    /**
     * Update the specified flag.
     */
    public function update(UpdateFlagRequest $request, int $id): JsonResponse
    {
        $flag = $this->flagService->getById($id);

        if (!$flag || $flag->tenant_id !== $request->user()->tenant_id) {
            throw new NotFoundHttpException('Flag not found.');
        }

        $this->flagService->update($flag, $request->validated());

        return response()->json([
            'message' => 'Flag updated successfully.',
            'data' => new FlagResource($flag->fresh()),
        ]);
    }

    /**
     * Remove the specified flag.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $flag = $this->flagService->getById($id);

        if (!$flag || $flag->tenant_id !== $request->user()->tenant_id) {
            throw new NotFoundHttpException('Flag not found.');
        }

        $this->flagService->delete($flag);

        return response()->json([
            'message' => 'Flag deleted successfully.',
        ]);
    }

    /**
     * Toggle flag enabled status.
     */
    public function toggle(Request $request, int $id): JsonResponse
    {
        $flag = $this->flagService->getById($id);

        if (!$flag || $flag->tenant_id !== $request->user()->tenant_id) {
            throw new NotFoundHttpException('Flag not found.');
        }

        $this->flagService->toggle($flag);

        return response()->json([
            'message' => 'Flag toggled successfully.',
            'data' => new FlagResource($flag->fresh()),
        ]);
    }
}
