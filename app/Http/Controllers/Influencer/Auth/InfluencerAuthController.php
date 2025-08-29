<?php

namespace App\Http\Controllers\Influencer\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Influencer\InfluencerLoginRequest;
use App\Http\Requests\Influencer\InfluencerRegisterRequest;
use App\Http\Resources\InfluencerResource;
use App\Models\Influencer;
use App\Services\Influencer\InfluencerRegisterService;
use App\Services\Influencer\MainService;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\Influencer\InfluencerProfileUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\PasswordResetInfluencerMail;
use Illuminate\Support\Facades\Hash;




class InfluencerAuthController extends Controller{
    protected InfluencerRegisterService $influencerRegisterService ;

    public function __construct(InfluencerRegisterService $influencerRegisterService){
        $this->influencerRegisterService = $influencerRegisterService;
    }

    public function register(InfluencerRegisterRequest $request){
        try{
            $influencerData= $this->influencerRegisterService->register($request->validated());
            return response()->json([
                'message' => 'Registration successful. Please verify your email.',
                "data"=>[
                    'influencer'=>new InfluencerResource($influencerData['influencer']),
                    // 'email_verification_code'=>$influencerData['email_verification_code'],
                    'email_verification_token'=>$influencerData['email_verification_token'],
                ],
            ], Response::HTTP_CREATED);
            
        }catch(\Exception $e){
            return response()->json([
                "message"=>$e->getMessage(),
                "error"=>$e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }




    public function verifyEmail(Request $request)
    {
        $this->validate($request, [
            'verification_code' => 'required|integer',
            'verification_token' => 'required|string',
        ]);
        $influencer = Influencer::query()->where('email_verification_token', $request->verification_token)
            ->where('email_verification_code', $request->verification_code)
            ->first();
        if (!$influencer) {
            return response()->json(['message' => 'Invalid verification code or token'], 400);
        }
        $influencer->update([
            'email_verified_at' => now(),
            'email_verification_code' => null,
            'email_verification_token' => null,
            'is_active' => true,
        ]);
        return response()->json(['message' => 'Email successfully verified.']);
    }

    

    public function login(InfluencerLoginRequest $request){
        try{
            $influencer = $this->influencerRegisterService->login($request->validated());


            $token = $influencer->createToken('admin_token')->plainTextToken;
            return response()->json([
                "message"=>"Successfully login",
                "data"=>new InfluencerResource($influencer),
                "token"=>$token
            ], Response::HTTP_CREATED);
        }catch(\Exception $e){
            return response()->json([
                "message"=>$e->getMessage(),
                "error"=>$e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    public function updateProfile(InfluencerProfileUpdateRequest $request){
         try{
            $influencer = $this->influencerRegisterService->updateProfile($request->validated());
            $token = $influencer->createToken('admin_token')->plainTextToken;
            return response()->json([
                "message"=>"Successfully updated",
                "data"=>new InfluencerResource($influencer),
                "token"=>$token
            ], Response::HTTP_CREATED);
        }catch(\Exception $e){
            return response()->json([
                "message"=>$e->getMessage(),
                "error"=>$e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }



    public function requestPasswordReset(Request $request)
        {

            try {
                $validator = Validator::make($request->all(), [
                    'email' => 'required|string|email|exists:influencers,email',
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'errors' => $validator->errors()->all()
                    ], 422);
                }

                $language = $request->header('Accept-Language') ?? 'ru';

                $customer = Influencer::query()->where('email', $request->email)->first();

                // if(!$customer->email_verified_at){
                //     return response()->json([
                //         'errors' => 'Emailinizi təsdiqləyin'
                //     ], 422);
                // }

                $resetToken = Str::random(60);

                $customer->update([
                    'password_reset_token' => $resetToken,
                    'password_reset_token_expiry' => now()->addHours(2),
                ]);

                

                $resetUrl =  'https://brendoo.com/'.$language . '/influencers/password-reset/' . $resetToken;

                Mail::to($customer->email)->send(new PasswordResetInfluencerMail($resetUrl));

                return response()->json(['message' => 'Password reset link sent to your email']);
            } catch (\Exception $exception) {
                \Log::error('Password Reset Email Error: ' . $exception->getMessage());
                return response()->json(['error' => 'Failed to send email.'], 500);
            }

    }



     public function resetPassword(Request $request)
    {

        $validator = Validator::make($request->all(), [
            // 'email' => 'required|string|email|exists:customers,email',
            'reset_token' => 'required',
            'new_password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()->all()
            ], 422);
        }

        $influencer = Influencer::query()->where('password_reset_token', $request->reset_token)->first();

        
        if (is_null($influencer)) {
            return response()->json(['message' => 'Invalid reset token'], 400);
        }

        if (!\Carbon\Carbon::parse($influencer->password_reset_token_expiry)->isFuture()) {
            return response()->json(['message' => 'Reset token expired'], 400);
        }

        $influencer->update([
            'password' => Hash::make($request->new_password),
            'password_reset_token' => null,
        ]);

        return response()->json(['message' => 'Password updated successfully']);

    }
}