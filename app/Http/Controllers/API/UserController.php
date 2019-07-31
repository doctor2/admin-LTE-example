<?php

namespace App\Http\Controllers\API;

use App\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
//        $this->authorize('isAdmin');
        if (Gate::allows('isAdmin') || Gate::allows('isAuthor')) {
            return User::latest()->paginate(5);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string|max:191',
            'email' => 'required|string|email|max:191|unique:users',
            'password' => 'required|string|min:8',
        ]);

        return User::create([
            'name' => $request['name'],
            'email' => $request['email'],
            'password' => Hash::make($request['password']),
            'bio' => $request['bio'],
            'photo' => $request['photo'],
            'type' => $request['type'],
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = auth('api')->user();

        $this->validate($request, [
            'name' => 'required|string|max:191',
            'email' => 'required|string|email|max:191|unique:users,email,' . $user->id,
            'password' => 'sometimes|required|string|min:8',
        ]);

        $currentPhoto = $user->photo;
        if ($request->photo != $currentPhoto) {
            preg_match_all('/data\:image\/([a-zA-Z]+)\;base64/', $request->photo, $matched);
            $ext = isset($matched[1][0]) ? $matched[1][0] : false;
            $imageName = sha1(time()) . '.' . $ext;

            \Image::make($request->photo)->save(public_path('images/profile/') . $imageName);

            $request->merge(['photo' => $imageName]);

            $oldPhoto = public_path('images/profile/') . $currentPhoto;
            if (file_exists($oldPhoto)) {
                @unlink($oldPhoto);
            }
        }
        if (!empty($request->password)) {
            $request->merge(['password' => Hash::make($request['password'])]);
        }

        $user->update($request->all());

        return ['message' => 'Success!'];
    }

    public function profile()
    {
        return auth('api')->user();
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $this->validate($request, [
            'name' => 'required|string|max:191',
            'email' => 'required|string|email|max:191|unique:users,email,' . $user->id,
            'password' => 'sometimes|required|string|min:8',
        ]);

        $user->update($request->all());

        return ['message' => 'Updated user info'];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->authorize('isAdmin');

        $user = User::findOrFail($id);

        $user->delete($id);

        return ['message' => 'User deleted'];
    }

    public function search()
    {
        if ($search = \Request::get('q')) {
            $users = User::where(function ($query) use ($search) {
                $query->where('name', 'LIKE', "%$search%")
                    ->orWhere('email', 'LIKE', "%$search%")
                    ->orWhere('type', 'LIKE', "%$search%");
            })->paginate(5);
        }else{
            return User::latest()->paginate(5);
        }

        return $users;
    }
}
