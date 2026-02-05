<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Request;
use App\Http\Response;
use App\Rbac\RbacRepository;

class PermissionMiddleware
{
    private RbacRepository $rbac;

    public function __construct(RbacRepository $rbac)
    {
        $this->rbac = $rbac;
    }

    public function handle(Request $request, string $requiredPermission): void
    {
        $userId = $request->getAttribute('user_id');
        if (!$userId) {
            Response::error('Unauthorized', 401);
        }

        $permissions = $this->rbac->getPermissions($userId);
        if (!in_array($requiredPermission, $permissions)) {
            Response::error('Forbidden: Insufficient permissions', 403);
        }
    }
}
