<?php

namespace App\Services\Influencer;

use App\Mail\VerificationCodeMail;
use App\Models\Influencer;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Mail;


class InfluencerRegisterService
{
   
    public function register(array $data)
{
    try {
        $verificationCode = rand(100000, 999999);
        $uuidToken = Str::uuid()->toString();

        $influencer = Influencer::where('email', $data['email'])->where('is_active', 1)->first();

        if ($influencer) {
            throw new \Exception('Bu email ilə aktiv hesab artıq mövcuddur.');
        }

        $influencer = Influencer::where('email', $data['email'])->first();

        if ($influencer) {
            $influencer->update([
                'name' => $data['name'],
                'phone' => $data['phone'],
                'social_profile' => $data['social_profile'],
                'status' => 'pending',
                // 'email_verification_code' => $verificationCode,
                // 'email_verification_token' => $uuidToken,
                'is_active'=>1,
                'password' => array_key_exists('password', $data) ? Hash::make($data['password']) : $influencer->password,
            ]);
        } else {
            // Yeni istifadəçi yaradılır
            $influencer = Influencer::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'social_profile' => $data['social_profile'],
                'status' => 'pending',
                'email_verification_code' => $verificationCode,
                'email_verification_token' => $uuidToken,
                'is_active'=>1,
                'password' => array_key_exists('password', $data) ? Hash::make($data['password']) : null,
            ]);
        }

        // Mail göndər
        // Mail::to($data['email'])->send(new VerificationCodeMail($verificationCode));

        return [
            'influencer' => $influencer,
            'email_verification_token' => $uuidToken,
        ];
    } catch (\Throwable $e) {
        Log::error('Influencer qeydiyyat xətası: ' . $e->getMessage());
        throw $e;
    }
    }



    public function login(array $data): ?Influencer{
        try {
            $influencer = Influencer::where('email', $data['email'])->first();
           
            if (!$influencer) {
                throw new \Exception('Influencer not found');
            }
           
            if (!Hash::check($data['password'], $influencer->password)) {
                throw new \Exception('Password is incorrect');
            }
            
            if (!$influencer->is_active) {
                throw new \Exception('Please verify your email address.', 401);
            }
            if ($influencer->status !== 'accepted') {
                throw new \Exception('Profile is not accepted');
            }
            return $influencer;
        } catch (\Throwable $e) {
            Log::error('Influencer login xətası: ' . $e->getMessage());
            throw $e;
        }
    }

    public function updateProfile(array $data): ?Influencer{
       
         try {
             $influencer = auth('influencers')->user();
            if (!$influencer) {
                throw new \Exception('Influencer not found');
            }
             $list = [
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
            ];
             if(array_key_exists('password', $data)){
                $list['password'] = Hash::make($data['password']);
            }
            $influencer->update($list);

            return $influencer;
        } catch (\Throwable $e) {
            Log::error('Influencer login xətası: ' . $e->getMessage());
            throw $e;
        }

    }
}
