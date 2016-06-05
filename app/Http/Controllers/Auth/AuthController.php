<?php

namespace App\Http\Controllers\Auth;

use App\User;
use Mockery\CountValidator\Exception;
use Validator;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;

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
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|min:6|confirmed',
            'apitoken' => 'required|unique:users,api_token'
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
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
            'api_token' => $data['apitoken']
        ]);
    }
    
    protected function adduser(Request $request) {
        $data = $request->all();
        $data += ['password_confirmation'=>$data['password']];

        if($this->validator($data)->fails()) {
            return response()->json(['status'=>'error','message'=>$this->validator($data)->messages()],400);
        } else {
            try {
                $this->create($data);
            } catch (\Exception $e) {
                \Log::error( $e->getMessage() );
                return response()->json(['status'=>'error','message'=>$e->getMessage()],400);
            }
        }

        return response()->json(['status'=>'success','message'=>'New user created.'],200);
    }
}
