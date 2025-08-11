<?php
namespace App\Http\Requests\Influencer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InfluencerRegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('influencers', 'email')->where('is_active', 1)->whereNotNull('email_verified_at'),
            ],
            'phone' => 'required|string|max:20',
            'social_profile' => 'required|max:255',
            'password' => 'required',
        ];
    }
}
