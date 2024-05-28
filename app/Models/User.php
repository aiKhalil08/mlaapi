<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Auth\Authenticatable as AuthTrait;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Traits\HasRoles;
use App\Contracts\CanReceiveOTPInterface;
use App\Traits\CanReceiveOTPTrait;
use App\Contracts\CanBeAffiliateInterface;
use App\Traits\AffiliateTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Observers\UserObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;


#[ObservedBy([UserObserver::class])]
class User extends Model implements Authenticatable, JWTSubject, CanReceiveOTPInterface, CanBeAffiliateInterface
{
    use HasFactory, AuthTrait, CanReceiveOTPTrait, HasRoles, AffiliateTrait;


    public $timestamps = false;
    public $guarded = ['id'];
    protected $hidden = ['id', 'password', 'pivot'];



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

    /**
     * Get all of the roles for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    // public function roles(): HasMany
    // {
    //     return $this->hasMany(Role::class);
    // }

    public function getIsAdminAttribute(): bool {
        foreach ($this->roles as $role) {
            if ($role->name == 'admin') return true;
        }
        return false;
    }

    public function getIsStudentAttribute(): bool {
        foreach ($this->roles as $role) {
            if ($role->name == 'student') return true;
        }
        return false;
    }


    public function getImagePathAttribute() {
        return explode('storage/', $this->image_url)[1];
    }

    public function getImageUrlAttribute(string | null $string) {
        if (!$string) return null;
        return Storage::url($string);
    }

    public function getNameAttribute() {
        return Str::ucfirst($this->first_name).' '.Str::ucfirst($this->last_name);
    }

    public function phoneNumber(): Attribute {
        return Attribute::make(
            set: function($value) {
                if (\preg_match('/^\+234[789]\d{9}$/', $value)) {
                    // if phone number is already correctly formatted, return it. e.g. when updating existing user model
                    return $value;
                }


                if (\preg_match('/^0\d{10}$/', $value)) {
                    // check if phone number contains leading zero and trim it away if true
                    $value = \substr($value, 1);
                }
        
                // append country code to phone number
                return '+234'.$value;
            },
        );
    }


    public function sendWelcomeEmail(): bool {

        
        $api_endpoint = 'https://mitiget.com.ng/mailerapi/message/singlemail';

        $title = 'Welcome to Mitiget Learning Academy - Spark Your Potential!';
        $message = view('emails.welcome', ['first_name'=>$this->first_name])->render();
        
        $data = [
            'title' => $title,
            
            'message' => $message,
            
            'email' => $this->email,
            
            'companyemail' => env('COMPANY_EMAIL'),
            
            'companypassword' => env('COMPANY_PASSWORD'),
        ];

        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($api_endpoint, $data) {
                
                $response = \Illuminate\Support\Facades\Http::post($api_endpoint, $data);
                
                if (!$response->ok()) {
                    throw new \Exception('couldn\'t send email');
                }
            });
            return true;
        } catch (\Exception $e) {
            return false;
        }

    }

    /**
     * Get the info associated with the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function info(): HasOne
    {
        return $this->hasOne(UserInfo::class);
    }

    public function history(): HasMany
    {
        return $this->hasMany(UserHistory::class, 'parent_id');
    }

    /**
     * Get all of the permissions history for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function permissionHistory(): HasMany
    {
        return $this->hasMany(PermissionHistory::class, 'parent_id');
    }

    /**
     * Get all of the roles history for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function roleHistory(): HasMany
    {
        return $this->hasMany(RoleHistory::class, 'parent_id');
    }

    public function hasVerifiedEmail() {
        return $this->email_verified != 0;
    }



    public function trails(): Morphs
    {
        return $this->Morphs(AuditTrail::class, 'actor');
    }

    public function scopeAreStudents(Builder $query) {
        $query->whereHas('roles', function (Builder $sub_query) {
            $sub_query->where('name', 'student');
        });
    }

    public function scopeAreAdmins(Builder $query) {
        $query->whereHas('roles', function (Builder $sub_query) {
            $sub_query->where('name', 'admin');
        });
    }

    public function scopeAreExternalUsers(Builder $query) {
        $query->whereHas('roles', function (Builder $sub_query) {
            $sub_query->where('name', 'external_user');
        });
    }

    public function scopeIsSuperAdmin(Builder $query) {
        $query->whereHas('roles', function (Builder $sub_query) {
            $sub_query->where('name', 'super_admin');
        });
    }

    public function scopeCanTakeQuiz(Builder $query) {
        $query->whereHas('roles', function (Builder $sub_query) {
            $sub_query->where('name', 'student')->orWhere('name', 'external_user');
        });
    }

    public function castToStudent(): Student {
        return new Student($this->makeVisible('id')->toArray());
    }

    public function castToExternalUser(): ExternalUser {
        return new ExternalUser($this->makeVisible('id')->toArray());
    }
}
