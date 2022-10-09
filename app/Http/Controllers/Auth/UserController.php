<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;

class UserController extends Controller
{
    public function index()
    {
        return view('users');
    }

    public function getList()
    {
        return User::all();
    }

    public function add(Request $request)
    {
        $registrar = app()->make('App\Services\Registrar');

        $validator = $registrar->validator($request->all());

		if ($validator->fails())
		{
			$this->throwValidationException(
				$request, $validator
			);
		}

        return $registrar->create($request->all());
    }

    public function update(Request $request)
    {
       $inputs = $request->all();

       $user = User::find($inputs['id']);
       $user->name = $inputs['name'];
       $user->email = $inputs['email'];
       $user->role = $inputs['role'];

       if (array_key_exists('new_password', $inputs)) {
            $user->password = bcrypt($inputs['new_password']);
       }

       $user->save();

       return $user;
    }

    public function delete(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $user->delete();
    }
}