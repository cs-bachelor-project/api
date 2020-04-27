<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Http\Resources\TaskResource;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (!$request->user()->hasAnyRole('admin', 'manager')) {
            return response()->json(['message' => 'You are not authorised to perform this action.'], 401);
        }

        return UserResource::collection(withRelations(User::filter($request)->paginate(10)->appends($request->except(['page', 'token']))))->response()->setStatusCode(200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!$request->user()->hasAnyRole('admin')) {
            return response()->json(['message' => 'You are not authorised to perform this action.'], 401);
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'roles' => 'required|array|min:1'
        ]);

        DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => bcrypt($data['password']),
            ]);

            $user->roles()->sync($data['roles']);
        });

        return response()->json(['message' => 'The User was created successfully.']);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, User $user)
    {
        if (!$request->user()->hasAnyRole('admin', 'manager')) {
            return response()->json(['message' => 'You are not authorised to perform this action.'], 401);
        }

        return response(new UserResource(withRelations($user)), 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        if (!$request->user()->hasAnyRole('admin')) {
            return response()->json(['message' => 'You are not authorised to perform this action.'], 401);
        }

        $data = $request->validate([
            'name' => 'string|max:255',
            'email' => "string|email|max:255|unique:users,email,{$user->id}",
        ]);

        $user->update($data);

        return response()->json(['message' => "{$user->name} was updated successfully."]);
    }

    /**
     * Change roles of the user
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function changeRoles(Request $request, User $user)
    {
        if (!$request->user()->hasAnyRole('admin')) {
            return response()->json(['message' => 'You are not authorised to perform this action.'], 401);
        }

        $data = $request->validate([
            'roles' => 'required|array|min:1'
        ]);

        $user->roles()->sync($data['roles']);

        return response()->json(['message' => "Roles for {$user->name} were updated successfully."]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, User $user)
    {
        if (!$request->user()->hasAnyRole('admin')) {
            return response()->json(['message' => 'You are not authorised to perform this action.'], 401);
        }

        $user->roles()->detach();

        $user->delete();

        return response()->json(['message' => "{$user->name} was deleted successfully."]);
    }

    /**
     * Display a listing of the resource's tasks.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function tasks(Request $request, User $user)
    {
        if (!$request->user()->hasAnyRole('admin', 'manager')) {
            return response()->json(['message' => 'You are not authorised to perform this action.'], 401);
        }

        return TaskResource::collection(withRelations($user->tasks()->filter($request)->paginate(10)->appends($request->except(['page', 'token']))))->response()->setStatusCode(200);
    }

    /**
     * Display a listing of the drivers.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function drivers(Request $request)
    {
        if (!$request->user()->hasAnyRole('admin', 'manager')) {
            return response()->json(['message' => 'You are not authorised to perform this action.'], 401);
        }

        $drivers = User::select('id', 'name')->whereHas('roles', function ($query) {
            $query->whereName('driver');
        })->get();

        return response()->json($drivers, 200);
    }
}
