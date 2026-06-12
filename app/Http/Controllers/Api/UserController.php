<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:Admin')->except(['index', 'show']);
        $this->middleware('company.scope');
    }

    public function index(Request $request)
    {
        $users = User::where('company_id', $request->user()->company_id)
            ->with('expenses')
            ->paginate(20);
            
        return response()->json($users);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => ['required', Rule::in(['Admin', 'Manager', 'Employee'])],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'company_id' => $request->user()->company_id,
            'role' => $request->role,
        ]);

        return response()->json([
            'message' => 'User created successfully',
            'user' => $user
        ], 201);
    }

    public function update(Request $request, User $user)
    {
        // Ensure user belongs to same company
        if ($user->company_id !== $request->user()->company_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => ['sometimes', 'email', Rule::unique('users')->ignore($user->id)],
            'role' => ['sometimes', Rule::in(['Admin', 'Manager', 'Employee'])],
        ]);

        $user->update($request->only(['name', 'email', 'role']));

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user
        ]);
    }
}