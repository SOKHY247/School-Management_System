<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search') ?? $request->query('name');
        $query = User::select('id', 'name', 'email');

        $query->when($search, function($q) use ($search){
            return $q->where('name', 'LIKE', "%$search%")->orWhere('email', 'LIKE', "%$search%");
        });

        $users = $query->forPage($request->page ?? 1, $request->limit ?? 2)->get();
        $count = User::count();

        if($users->isEmpty()){
            return response()->json(['message' => 'Users not found'], 404);
        }

        $current_user = Auth::user();
        return response()->json([
            'message'      => 'Users retrieved successfully',
            'total'        => $count,
            'current_user' => $current_user->name,
            'users'        => $users
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string',
            'email'    => 'required|string|unique:users,email',
            'password' => 'required|string|confirmed'
        ]);

        $user = User::create([
            'name'     => $request->input('name'),
            'email'    => $request->input('email'),
            'password' => Hash::make($request->input('password')),
        ]);

        return response()->json([
            'message' => 'User created successfully',
            'user'    => $user
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|string',
            'password' => 'required|string'
        ]);

        $user = User::where('email', $request->input('email'))->first();

        if(!$user){
            return response()->json(['message' => 'User not found'], 404);
        }

        if(!Hash::check($request->input('password'), $user->password)){
            return response()->json(['message' => 'Invalid email or password!'], 401);
        }

        // Delete old tokens before creating new one (prevent token buildup)
        $user->tokens()->delete();

        $access_token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message'      => 'Login successful',
            'user'         => $user,
            'access_token' => $access_token,
            'token_type'   => 'Bearer'
        ], 200);
    }

   public function logout(Request $request)
{
    $user = $request->user();

    if (!$user) {
        return response()->json(['message' => 'Unauthenticated'], 401);
    }
    // Delete only the current token (safer than deleting all)
    $request->user()->currentAccessToken()->delete();

    return response()->json(['message' => 'User logged out successfully'], 200);
}

    public function show(string $id)
    {
        $user = User::find($id);

        if(!$user){
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json([
            'message' => 'User retrieved successfully',
            'user'    => $user
        ]);
    }

    public function update(Request $request, string $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if ($user->id != Auth::id()) {
            return response()->json([
                'message' => 'Cannot update this user, make sure the logged in user is the owner.'
            ], 403);
        }

        $request->validate([
            'name'     => 'sometimes|string',
            'email'    => 'sometimes|string|email|unique:users,email,' . $id,
            'password' => 'sometimes|string|min:8|confirmed',
        ]);

        if ($request->has('name'))     $user->name     = $request->input('name');
        if ($request->has('email'))    $user->email    = $request->input('email');
        if ($request->has('password')) $user->password = Hash::make($request->input('password'));

        $user->save();

        return response()->json([
            'message' => 'User updated successfully',
            'user'    => $user
        ]);
    }

    public function destroy(string $id)
    {
        $user = User::find($id);

        if(!$user){
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }
}