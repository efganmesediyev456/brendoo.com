<?php
namespace App\Http\Requests\Influencer;

use Illuminate\Foundation\Http\FormRequest;

class InfluencerProfileUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:influencers,email,'.auth('influencers')->user()->id,
            'phone' => 'required|string|max:20',
            'password' => 'sometimes|confirmed',
            'password_confirmation' => 'required_with:password',
        ];
    }
}
