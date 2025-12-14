<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSSOUserRequest;
use App\Services\SSOUserService;
use Illuminate\Http\Request;

class SSOUserController extends Controller
{
    public function __construct(protected SSOUserService $ssoUserService) {}

    public function index(Request $request)
    {
        $filters = [
            'user_id' => $request->get('user_id'),
            'created_by' => $request->get('created_by'),
        ];

        $ssoUsers = $this->ssoUserService->getAllSSOUsers($filters);

        return view('sso-users.index', compact('ssoUsers'));
    }

    public function store(StoreSSOUserRequest $request)
    {
        $this->ssoUserService->create(['user_id' => $request->user_id, 'created_by' => auth()->id()]);

        return redirect()->route('sso-users.index')->with('success', 'SSO User created successfully.');
    }

    public function destroy($id)
    {
        $this->ssoUserService->delete($id);

        return redirect()->route('sso-users.index')->with('success', 'SSO User deleted successfully.');
    }
}
