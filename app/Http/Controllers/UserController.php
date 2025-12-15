<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(protected UserService $userService) {}

    public function index(Request $request)
    {
        $filters = [
            'status' => $request->get('status'),
            'search' => $request->get('search'),
        ];

        $data = $this->userService->getAllUsers($filters);
        $users = $data['users'];
        $totalUsers = $data['totalUsers'];
        $activeUsers = $data['activeUsers'];
        $inactiveUsers = $data['inactiveUsers'];
        $adminUsers = $data['adminUsers'];

        return view('users.index', compact('users', 'totalUsers', 'activeUsers', 'inactiveUsers', 'adminUsers'));
    }

    public function show($id)
    {
        $user = $this->userService->getUserById($id);
        $familyMembers = $this->userService->getFamilyMembers($user->membership_code, $user->id);

        return view('users.show', compact('user', 'familyMembers'));
    }

    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'action' => 'required|string|in:activate,deactivate,delete',
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:users,id',
        ]);

        $action = $request->action;
        $userIds = $request->ids;

        try {
            switch ($action) {
                case 'activate':
                    $this->userService->bulkUpdateStatus($userIds, true);
                    $message = __('messages.bulk_activate_success');
                    break;
                case 'deactivate':
                    $this->userService->bulkUpdateStatus($userIds, false);
                    $message = __('messages.bulk_deactivate_success');
                    break;
            }

            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.bulk_action_error'),
            ], 500);
        }
    }
}
