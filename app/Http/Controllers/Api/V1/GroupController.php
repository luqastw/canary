<?php

namespace App\Http\Controllers\Api\V1;

use App\Contracts\Services\GroupServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Group\CreateGroupRequest;
use App\Http\Requests\Group\UpdateGroupRequest;
use App\Http\Resources\GroupResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GroupController extends Controller
{
    public function __construct(
        private GroupServiceInterface $groupService
    ) {}

    /**
     * Display a listing of groups.
     */
    public function index(Request $request): JsonResponse
    {
        $groups = $this->groupService->getAll($request->user()->tenant_id);

        return response()->json([
            'data' => GroupResource::collection($groups),
            'meta' => [
                'total' => $groups->count(),
            ],
        ]);
    }

    /**
     * Store a newly created group.
     */
    public function store(CreateGroupRequest $request): JsonResponse
    {
        $group = $this->groupService->create(
            $request->user()->tenant_id,
            $request->validated()
        );

        return response()->json([
            'message' => 'Group created successfully.',
            'data' => new GroupResource($group),
        ], 201);
    }

    /**
     * Display the specified group.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $group = $this->groupService->getById($id);

        if (!$group || $group->tenant_id !== $request->user()->tenant_id) {
            throw new NotFoundHttpException('Group not found.');
        }

        return response()->json([
            'data' => new GroupResource($group),
        ]);
    }

    /**
     * Update the specified group.
     */
    public function update(UpdateGroupRequest $request, int $id): JsonResponse
    {
        $group = $this->groupService->getById($id);

        if (!$group || $group->tenant_id !== $request->user()->tenant_id) {
            throw new NotFoundHttpException('Group not found.');
        }

        $this->groupService->update($group, $request->validated());

        return response()->json([
            'message' => 'Group updated successfully.',
            'data' => new GroupResource($group->fresh()),
        ]);
    }

    /**
     * Remove the specified group.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $group = $this->groupService->getById($id);

        if (!$group || $group->tenant_id !== $request->user()->tenant_id) {
            throw new NotFoundHttpException('Group not found.');
        }

        $this->groupService->delete($group);

        return response()->json([
            'message' => 'Group deleted successfully.',
        ]);
    }
}
