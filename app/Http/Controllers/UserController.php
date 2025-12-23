<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

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

    public function groups(Request $request, $id)
    {
        $q = $request->input('q');
        $perPage = (int) $request->input('per_page', 20);

        $user = $this->userService->getUserById($id);
        $memberIds = $user->groups->pluck('id')->toArray();

        $query = \App\Models\Group::query();

        if ($q) {
            $query->where('name', 'like', "%{$q}%")
                ->orWhere('abbreviation', 'like', "%{$q}%");
        }

        $groups = $query->select('id', 'name', 'abbreviation')
            ->orderBy('name')
            ->paginate($perPage)
            ->appends($request->only('q', 'per_page'));

        $groups->getCollection()->transform(function ($group) use ($memberIds) {
            $group->is_member = in_array($group->id, $memberIds);

            return $group;
        });

        return response()->json($groups);
    }

    public function manageGroups(Request $request, $id)
    {
        $request->validate([
            'group_ids' => 'nullable|array',
            'group_ids.*' => 'exists:groups,id',
        ]);

        $user = $this->userService->getUserById($id);

        $this->userService->syncGroups($user, $request->input('group_ids', []));

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('users.show', $user->id)->with('success', 'User groups updated successfully.');
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

    public function generateQr($id)
    {
        try {
            $user = $this->userService->getUserById($id);
            $qrCodeString = $user->qrCode();

            $qrCode = QrCode::size(150)->format('svg')->generate($qrCodeString);

            return response()->json([
                'success' => true,
                'qr_code' => (string) $qrCode,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.error_generating_qr'),
            ], 500);
        }
    }
}
