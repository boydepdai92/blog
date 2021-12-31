<?php

namespace App\Http\Controllers\Console;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\Users\LoginRequest;
use App\Http\Requests\Users\RegisterRequest;
use App\Repositories\Contracts\UserRepositoryInterface;

class AuthController extends Controller
{
    protected $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function index()
    {
        return view('users.login');
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            return redirect('console')->withSuccess('Login in successfully');
        }

        return redirect('console/login')->withErrors('Username or password is invalid');
    }

    public function registration()
    {
        return view('users.register');
    }

    public function register(RegisterRequest $request)
    {
        $params = $request->only(['name', 'email', 'password']);

        $params['password'] = Hash::make($params['password']);

        DB::beginTransaction();

        /** @var User $user */
        $user = $this->userRepository->create($params);

        $adminEmail = config('permission.admin_email');

        if ($user->email == $adminEmail) {
            $user->roles()->sync([config('permission.super_admin_role')]);
        } else {
            $user->roles()->sync([config('permission.default_role')]);
        }

        DB::commit();

        return redirect('console/login')->withSuccess('You registered successfully');
    }

    public function logout()
    {
        Session::flush();
        Auth::logout();

        return redirect('console/login');
    }
}
