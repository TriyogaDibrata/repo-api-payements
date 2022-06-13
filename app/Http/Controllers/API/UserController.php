<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Rules\MatchOldPassword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Validator;

class UserController extends Controller
{
    public function index() {
        $users = User::latest()->get();
        return response()->json(['success' => true, 'data' => $users, 'msg' => 'Users fetched.']);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'username' => 'required|string|max:255|unique:users'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors());       
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'password' => Hash::make($request->password),
            'username' => $request->username
         ]);

        // $token = $user->createToken('auth_token')->plainTextToken;

        return response()
            ->json(['data' => $user, 'success' => true, 'msg' => 'User successfully added']);
    }

    public function show($id)
    {
        $user = User::find($id);
        if (is_null($user)) {
            return response()->json('Data not found', 404); 
        }
        return response()->json(['success' => true, 'data' => $user]);
    }

    public function update(Request $request, User $user)
    {
        $validator = Validator::make($request->all(),[
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'username' => 'required|string|max:255'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors());       
        }

        $user->name = $request->name;
        $user->email = $request->email;
        $user->username = $request->username;
        $user->role = $request->role;
        
        $user->save();
        
        return response()->json(['success' => true, 'msg' => 'User successfully updated.', 'data' => $user]);
    }

    public function destroy(User $user)
    {
        $user->delete();

        return response()->json(['msg' => 'User deleted successfully', 'success' => true]);
    }

    public function updatePassword(Request $request) {
        $request->validate([
            'old_password' => ['required', new MatchOldPassword],
            'new_password' => ['required'],
            'c_new_password' => ['same:new_password'],
        ]);

        $user = User::find(auth()->user()->id);
        $user->password = Hash::make($request->new_password);
        $user->save();
        
        return response()->json(['msg' => 'Password updated successfully', 'success' => true, 'data' => $user]);
    }
}
