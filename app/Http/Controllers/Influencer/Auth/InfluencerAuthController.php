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
}