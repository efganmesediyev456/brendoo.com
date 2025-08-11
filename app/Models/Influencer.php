<?php

namespace App\Models;

use App\Traits\LogsActivityTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class Influencer extends Model
{

    use HasFactory, Notifiable, HasRoles, HasApiTokens,LogsActivityTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'is_active',
        'is_blocked',
        'birthday',
        'password',
        'phone',
        'gender',
        'email_verification_code',
        'email_verification_token',
        'password_reset_token_expiry',
        'password_reset_token',
        'email_verified_at',
        'social_profile',
        'status'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];


    public function getFullNameAttribute(){
        return $this->name.' '.$this->surname.'( '.$this->email.' )';
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */

     public function coupons(){
        return $this->hasMany(Coupon::class);
     }
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }


    public function collections(){
        return $this->hasMany(Collection::class);
    }

    public function products(){
        return $this->hasMany(InfluencerCollectionProduct::class,'influencer_id');
    }

    public function collectionProducts($id){
        return $this->products()->where('collection_id', $id);
    }

    public function balances(){
        return $this->hasMany(Balance::class);
    }


    public function typeBalancesValue($type){
        return $this->balances()->where('balance_type', $type)->orderBy('id','desc')->first();
    }

    public function typeBalanceValues($type){
        return $this->balances()->where('balance_type', $type)->orderBy('id','desc');
    }


    public function demandPayments(){
        return $this->hasMany(DemandPayment::class,'influencer_id');
    }


    public function stories(){
        return $this->hasMany(Story::class);
    }
}
