<?php

namespace App\Http\Controllers\Auth;

use App\Model\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Validator;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;

use Auth;
use File;

class AuthController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Registration & Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users, as well as the
    | authentication of existing users. By default, this controller uses
    | a simple trait to add these behaviors. Why don't you explore it?
    |
    */

    use AuthenticatesAndRegistersUsers, ThrottlesLogins;

    /**
     * Where to redirect users after login / registration.
     *
     * @var string
     */
    protected $redirectTo = '/';

    protected $username = 'username';

    /**
     * Create a new authentication controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware($this->guestMiddleware(), ['except' => 'logout']);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|max:255',
            'username' => 'required|max:255|unique:' . \CreateUserTable::$tableName,
            'email' => 'required|email|max:255|unique:' . \CreateUserTable::$tableName,
            'password' => 'required',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    protected function create(array $data)
    {
        $aryParam = [
            'name'      => $data['name'],
            'username'  => $data['username'],
            'email'     => $data['email'],
            'password'  => bcrypt($data['password']),
            'birthday'  => $data['birthday'],
            'gender'    => $data['gender'],
        ];

        // if photo file exists, save file first
        if (array_has($data, 'photo')) {
            $filePhoto = $data['photo'];

            // create user photo directory, if not exist
            if (!file_exists(getUserPhotoPath())) {
                File::makeDirectory(getUserPhotoPath(), 0777, true);
            }

            // generate file name u**********.ext
            $strName = 'u' . time() . uniqid() . '.' . $filePhoto->getClientOriginalExtension();

            // move file to upload folder
            $filePhoto->move(getUserPhotoPath(), $strName);

            // add to database
            $aryParam['photo'] = $strName;
        }

        return User::create($aryParam);
    }

    /**
     * Redefined for signup api
     * @param Request $request
     * @return User
     */
    public function register(Request $request)
    {
        $validator = $this->validator($request->all());

        // failed validation
        if ($validator->fails()) {
            $error = json_decode($validator->errors());
            return response()->json($error, softFailStatus());
        }

        return $this->create($request->all());
    }

    /**
     * Redefined for login api
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request)
    {
        $credentials = $this->getCredentials($request);

        // SessionGuard, why??
        $guard = Auth::guard($this->getGuard());

        if ($guard->attempt($credentials, $request->has('remember'))) {
            return $guard->user();
        }

        return response()->json(null, softFailStatus());
    }
}
