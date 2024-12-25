<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Models\Admin;
use App\Models\Broker;
use Ichtrojan\Otp\Otp;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\Auth\ResetPasswordRequest;

class ResetPasswordController extends Controller
{
    private $otp;

    public function __construct(){
        $this->otp = new Otp;
    }

    public function resetPassword(ResetPasswordRequest $request){

        $otp2 = $this->otp->validate($request->phoNum, $request->otp);
        if (!$otp2->status) {
            return response()->json(['error' => $otp2], 401);
        }


        $user = User::where('phoNum', $request->phoNum)->first();
        if ($user) {

            $user->update(['password' => Hash::make($request->password)]);
        } else {
            $admin = Admin::where('phoNum', $request->phoNum)->first();
            if ($admin) {

                $admin->update(['password' => Hash::make($request->password)]);
            } else {
                $broker = Broker::where('phoNum', $request->phoNum)->first();
                if ($broker) {

                    $broker->update(['password' => Hash::make($request->password)]);
                } else {
                    return response()->json(['error' => 'User, Admin, or Broker not found.'], 404);
                }
            }
        }

        return response()->json([
            'message' => "The password reset successfully."
        ]);
    }
}
