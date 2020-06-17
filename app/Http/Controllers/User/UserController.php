<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\TaskResource;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use App\Traits\MaxDrivers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    use MaxDrivers;

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
        
        if (in_array(3, $request->get('roles'))) {
            if ($this->notReachedMaxDrivers() != false) {
                return $this->notReachedMaxDrivers();
            }
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|min:6|confirmed',
            'roles' => 'required|array|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()], 422);
        }

        DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->get('name'),
                'email' => $request->get('email'),
                'password' => bcrypt($request->get('password')),
            ]);

            $user->roles()->sync($request->get('roles'));
        });

        return response()->json(['message' => 'The user was created successfully.']);
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

        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255',
            'email' => "email|max:255|unique:users,email,{$user->id}",
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()], 422);
        }

        $user->update($request->all());

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

        if (in_array(3, $request->get('roles'))) {
            if ($this->notReachedMaxDrivers() != false) {
                return $this->notReachedMaxDrivers();
            }
        }
        
        $validator = Validator::make($request->all(), [
            'roles' => 'required|array|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()], 422);
        }

        $user->roles()->sync($request->get('roles'));

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
