<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Auth\Authenticatable as AuthTrait;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Admin extends Model implements Authenticatable, JWTSubject
{
    use HasFactory, AuthTrait;


    public $timestamps = false;
    public $guarded = ['id'];
    protected $hidden = ['id'];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function getTypeAttribute() {
        return 'admin';
    }
}
