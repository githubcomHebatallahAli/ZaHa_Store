<?php

namespace App\Http\Controllers\Auth;

use App\Models\Admin;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\Auth\AdminRegisterRequest;
use App\Http\Resources\Auth\AdminRegisterResource;
use Carbon\Carbon;

class AdminAuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        $validator = Validator::make($request->all(), $request->rules());


        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if (!$token = auth()->guard('admin')->attempt($validator->validated())) {
            return response()->json([
                'message' => 'Invalid data'
            ], 422);

            $admin = auth()->guard('admin')->user();

            // if (is_null($admin->email_verified_at)) {
            //     return response()->json([
            //         'message' => 'Email not verified. Please verify it.'
            //     ], 403);
            // }
        }

        $admin = auth()->guard('admin')->user();
        if ($admin->ip !== $request->ip()) {
            $admin->ip = $request->ip();
            $admin->save();
        }

        $admin->update([
            'last_login_at' => Carbon::now()->timezone('Africa/Cairo')
        ]);

        return $this->createNewToken($token);
    }

    /**
     * Register an Admin.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    // Register an Admin.
    public function register(AdminRegisterRequest $request)
    {
        if (!Gate::allows('create', Admin::class)) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        $validator = Validator::make($request->all(), $request->rules());

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }


        $adminData = array_merge(
            $validator->validated(),
            ['password' => bcrypt($request->password)],
    ['ip' => $request->ip()]
        );



        $admin = Admin::create($adminData);

        if ($request->hasFile('image')) {

            $path = $request->file('image')->store('admin', 'public');
            $admin->image()->create(['path' => $path]);
        }

        $admin->load('image');

        $admin->save();
        // $admin->notify(new EmailVerificationNotification());

        return response()->json([
            'message' => 'Admin Registration successful',
            'admin' =>new AdminRegisterResource($admin)
        ]);
    }




    /**
     * Log the admin out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        // التحقق من إذن المستخدم لتسجيل الخروج
        if (!Gate::allows('logout', Admin::class)) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        $admin = auth()->guard('admin')->user();

        if ($admin) {
            if ($admin->last_login_at) {
                $sessionDuration = Carbon::parse($admin->last_login_at)->diffInSeconds(Carbon::now());

                $admin->update([
                    'last_logout_at' => Carbon::now(),
                    'session_duration' => $sessionDuration
                ]);
            }

            auth()->guard('admin')->logout();
            return response()->json([
                'message' => 'Admin successfully signed out',
                'last_logout_at' => Carbon::now()->toDateTimeString(),
                'session_duration' => gmdate("H:i:s", $sessionDuration)
            ])->cookie('token', '', -1);
        }

        return response()->json([
            'message' => 'Admin not found'
        ], 404);
    }


    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->createNewToken(auth()->guard('admin')->refresh());
    }

    /**
     * Get the authenticated Admin.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function userProfile()
    {
        return response()->json([
        "data" => auth()->guard('admin')->user()
        ]);
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function createNewToken($token)
    {
        $admin = auth()->guard('admin')->user();
        $admin->last_login_at = Carbon::parse($admin->last_login_at)
        ->timezone('Africa/Cairo')->format('Y-m-d H:i:s');
        $admin = Admin::with('role:id,name')->find(auth()->guard('admin')->id());
        return response()->json([

            // 'access_token' => $token,
            // 'token_type' => 'bearer',
            // 'expires_in' => auth()->guard('admin')->factory()->getTTL() * 60,
            'admin' => $admin,
            'message' => 'Logged in successfully',
        ])->cookie('token', $token, 720, null, null, false, true);
    }
}
