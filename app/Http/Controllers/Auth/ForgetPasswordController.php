<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Models\Admin;
use App\Models\Broker;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Notifications\ResetPasswordNotification;
use App\Http\Requests\Auth\ForgetPasswordRequest;

class ForgetPasswordController extends Controller
{
    public function forgotPassword(ForgetPasswordRequest $request){
        $input = $request->only('phoNum');
        $user = User::where('phoNum',$input)->first();
        $admin = Admin::where('phoNum',$input)->first();
        $broker = Broker::where('phoNum',$input)->first();
        if (!$user && !$admin && !$broker) {
            return response()->json([
                'message' => "User or Admin or Broker not found."
            ], 404);
        }

        if ($user) {
            $user->notify(new ResetPasswordNotification());
        }


        if ($admin) {
             $admin->notify(new ResetPasswordNotification());
        }

        if ($broker) {
             $broker->notify(new ResetPasswordNotification());
        }

        return response()->json([
            'message' => "Please check your SMS."
        ]);

    }
}
