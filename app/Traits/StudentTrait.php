<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Course;
use App\Models\Cohort;
use App\Models\Event;
use App\Models\Sale;
use App\Models\Cart;
use App\Models\Certificate;
use App\Models\ReferralCode;
use Illuminate\Support\Facades\DB;

trait StudentTrait {

    /**
     * Get the studentInfo associated with the StudentTrait
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function studentInfo(): HasOne
    {
        return $this->hasOne(StudentInfo::class, 'user_id');
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

    public function hasVerifiedEmail(): bool {
        return $this->email_verified != 0;
    }

    /**
     * Get all of the carts for the Student
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class, 'user_id');
    }


    /**
     * The events that belong to the Student
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function events(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'event_watchlist', 'user_id');
    }


    /**
     * Get all of the purchases for the Student
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function purchases(): HasMany
    {
        return $this->hasMany(Sale::class, 'user_id', 'id');
    }


    /**
     * The cohorts that belong to the Student
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function cohorts(): BelongsToMany
    {
        return $this->belongsToMany(Cohort::class, 'cohort_user', 'user_id', 'cohort_id');
    }


    /**
     * Get all of the certificates for the Student
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class, 'user_id');
    }

    /**
     * Get a the certificate of a single cohort or course for the Student
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function certificate(): HasOne
    {
        return $this->hasOne(Certificate::class, 'user_id');
    }
   

    
    public function cartedCourses(): array
    {
        $certificate_courses = DB::select('select course.code, course.title, concat(\''.request()->schemeAndHttpHost().'/storage/\',course.image_url) as image_url, json_length(course.modules) as number_of_modules from certificate_courses as course inner join carts on carts.course_id = course.id where carts.course_type = "App\\\Models\\\CertificateCourse" and carts.user_id = ?', [$this->id]);

        $certification_courses = DB::select('select course.code, course.title, concat(\''.request()->schemeAndHttpHost().'/storage/\',course.image_url) as image_url, json_length(course.modules) as number_of_modules from certification_courses as course inner join carts on carts.course_id = course.id where carts.course_type = "App\\\Models\\\CertificationCourse" and carts.user_id = ?', [$this->id]);

        $offshore_courses = DB::select('select course.title, concat(\''.request()->schemeAndHttpHost().'/storage/\',course.image_url) as image_url, json_length(course.modules) as number_of_modules from offshore_courses as course inner join carts on carts.course_id = course.id where carts.course_type = "App\\\Models\\\OffshoreCourse" and carts.user_id = ?', [$this->id]);

        $carted_courses = ['certificate_courses'=>$certificate_courses, 'certification_courses'=>$certification_courses, 'offshore_courses'=>$offshore_courses];

        return $carted_courses;
    }


    public function cartedCoursesTitles(): array {

        $certificate_courses = DB::select('select code from certificate_courses inner join carts on carts.course_id = certificate_courses.id and carts.user_id = ?', [$this->id]);

        $certification_courses = DB::select('select code from certification_courses inner join carts on carts.course_id = certification_courses.id and carts.user_id = ?', [$this->id]);

        $offshore_courses = DB::select('select title from offshore_courses inner join carts on carts.course_id = offshore_courses.id and carts.user_id = ?', [$this->id]);

        $carted_courses = ['certificate_courses'=>$certificate_courses, 'certification_courses'=>$certification_courses, 'offshore_courses'=>$offshore_courses];

        return $carted_courses;
    }

}