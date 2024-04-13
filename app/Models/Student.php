<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Auth\Authenticatable as AuthTrait;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Contracts\CanReceiveOTP;
use App\Traits\CanReceiveOTPTrait;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Sinergi\Token\StringGenerator;
use Illuminate\Support\Facades\Storage;

// use Illuminate\Database\Eloquent\Relations\MorphOne;

class Student extends Model implements Authenticatable, JWTSubject, CanReceiveOTP
{
    use HasFactory, AuthTrait, CanReceiveOTPTrait;

    public $timestamps = false;
    public $guarded = ['id'];
    protected $hidden = ['id', 'password'];
    // private string $generated_referral_code;



    public function send_welcome_email(): bool {

        
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



    public function getImagePathAttribute() {
        return explode('storage/', $this->image_url)[1];
    }

    public function getImageUrlAttribute(string | null $string) {
        if (!$string) return null;
        return Storage::url($string);
        // return request()->schemeAndHttpHost().'/storage/'.$string;
    }

    public function getNameAttribute() {
        return Str::ucfirst($this->first_name).' '.Str::ucfirst($this->last_name);
    }


    public function hasVerifiedEmail() {
        return $this->email_verified != 0;
    }

    public function getTypeAttribute() {
        return 'student';
    }



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

    // public function refresh_token():MorphOne {
    //     return $this->morphOne(RefreshToken::class, 'tokenable');
    // }   


    // public function send_otp(int $otp) {

         
    // }

    /**
     * Get all of the comments for the Student
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class);
    }


    /**
     * The events that belong to the Student
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function events(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'event_watchlist');
    }


    public function generate_referral_code() {
        return StringGenerator::randomAlnum(8);
    }

    /**
     * Get the referrer_code associated with the Student
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function referral_codes(): HasMany
    {
        return $this->hasMany(ReferralCode::class,);
    }
    
    /* retrieves latest referral code */
    public function getReferralCodeAttribute() {
        // return $this->hasOne(ReferralCode::class)->latestOfMany();
        $referral_code = $this->referral_codes()->where('validity', 1)->first();

        if (!$referral_code) return null;
        else return $referral_code;
    }


    public function is_affiliate() {
        return $this->referral_code != null;
    }

    /**
     * Get all of the purchases for the Student
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function purchases(): HasMany
    {
        return $this->hasMany(Sale::class, 'student_id', 'id');
    }


    /**
     * Get all of the referrals for the Student
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function referrals(): HasManyThrough
    {
        return $this->through('referral_codes')->has('referrals');
    }


    /**
     * The cohorts that belong to the Student
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function cohorts(): BelongsToMany
    {
        return $this->belongsToMany(Cohort::class);
    }


    /**
     * Get all of the certificates for the Student
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }


    /**
     * Get all of the fulfillments for the Student
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function fulfillments(): HasMany
    {
        return $this->hasMany(Fulfillment::class);
    }


    public function referral_code_has_expired() {
        if (!$this->is_affiliate()) return true;
        return \Carbon\Carbon::now() > $this->referral_code->expires_at;
    }

    public function referral_count() {
        // var_dump($this->referrals->count()); return null; 
        return $this->referrals->count();
    }

    public function getTotalCommissionAttribute() {
        

        $total_commission = DB::select('select sum(referrals.commission) as total_commission from referrals inner join students on students.id = referrals.referrer_id where referrals.referrer_id = ?', [$this->id]);

        return $total_commission[0]->total_commission;

    }

    public function getCompletedPayoutAttribute() { // all completed commission withdrawal


        return $this->fulfillments()->select(DB::raw('sum(amount) as amount'))->where('status', 1)->first()['amount'];
    }


    public function getPendingPayoutAttribute() { // pending payout requests
        return $this->fulfillments()->where('status', '0')->select(DB::raw('sum(amount) as amount'))->first()['amount'];
    }


    public function getWithdrawableAmountAttribute() {
        return (int) $this->total_commission - (int) $this->pending_payout - (int) $this->completed_payout;
    }


    public function affiliate_portal() {
        return [
            'referral_code' => $this->referral_code->code,
            'is_expired' => $this->referral_code_has_expired(),
            'total_referrals' => $this->referral_count(),
            'total_commission' => $this->total_commission,
            'completed_payout' => $this->completed_payout,
            'referral_code_commission_percentage' => $this->referral_code->commission,
            'payout_history' => $this->fulfillments()->select('amount', 'type', 'status', 'date_added')->orderByDesc('date_added')->get(),
            'pending_payout' => $this->pending_payout,
            'withdrawable_amount' => $this->withdrawable_amount,
        ];
    }
   

    
    public function carted_courses()
    {
        $certificate_courses = DB::select('select course.code, course.title, concat(\''.request()->schemeAndHttpHost().'/storage/\',course.image_url) as image_url, json_length(course.modules) as number_of_modules from certificate_courses as course inner join carts on carts.course_id = course.id and carts.student_id = ?', [$this->id]);

        $certification_courses = DB::select('select course.code, course.title, concat(\''.request()->schemeAndHttpHost().'/storage/\',course.image_url) as image_url, json_length(course.modules) as number_of_modules from certification_courses as course inner join carts on carts.course_id = course.id and carts.student_id = ?', [$this->id]);

        $offshore_courses = DB::select('select course.title, concat(\''.request()->schemeAndHttpHost().'/storage/\',course.image_url) as image_url, json_length(course.modules) as number_of_modules from offshore_courses as course inner join carts on carts.course_id = course.id and carts.student_id = ?', [$this->id]);

        $carted_courses = ['certificate_courses'=>$certificate_courses, 'certification_courses'=>$certification_courses, 'offshore_courses'=>$offshore_courses];

        return $carted_courses;
    }


    public function get_carted_course($category, $identity) {
        $category = explode('_', $category)[0];
        $table = $category.'_courses';
        $course_type = 'App\Models\\'.str()->ucfirst($category).'Course';
        
        if ($category == 'certificate' || $category == 'certification') {
            $statement = "select $table.code, $table.title, $table.modules, $table.overview, $table.objectives, $table.attendees, $table.prerequisites, concat('".request()->schemeAndHttpHost()."/storage/', $table.image_url) as image_url from $table inner join carts on $table.id = carts.course_id where carts.student_id = ? and $table.code = '$identity'";
        } else if ($category == 'offshore') {
            $statement = "select $table.title, $table.modules, $table.overview, $table.objectives, $table.attendees, $table.prerequisites, concat('".request()->schemeAndHttpHost()."/storage/', $table.image_url) as image_url from $table inner join carts on $table.id = carts.course_id where carts.student_id = ? and $table.title = '$identity'";
        }
        // $course = DB::select($statement, [$this->id]);
        // var_dump($course, $statement); return null;
        if ($course = DB::select($statement, [$this->id])) return $course;
        else return false;
    }

    


    public function carted_courses_titles() {

        $certificate_courses = DB::select('select code from certificate_courses inner join carts on carts.course_id = certificate_courses.id and carts.student_id = ?', [$this->id]);

        $certification_courses = DB::select('select code from certification_courses inner join carts on carts.course_id = certification_courses.id and carts.student_id = ?', [$this->id]);

        $offshore_courses = DB::select('select title from offshore_courses inner join carts on carts.course_id = offshore_courses.id and carts.student_id = ?', [$this->id]);

        $carted_courses = ['certificate_courses'=>$certificate_courses, 'certification_courses'=>$certification_courses, 'offshore_courses'=>$offshore_courses];

        return $carted_courses;
    }


    public static function get_affiliates() {
        $affiliates = DB::select('select s.first_name, s.last_name, s.email, r.code referral_code from students s inner join referral_codes r on s .id = r.student_id where r.validity = 1');

        return $affiliates;
    }
}
