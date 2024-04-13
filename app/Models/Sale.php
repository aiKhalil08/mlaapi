<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

class Sale extends Model
{
    use HasFactory;

    public $timestamps = false;
    public $guarded = ['id'];
    // protected $hidden = ['id'];

    /**
     * Get the student that owns the Sale
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function course(): MorphTo {
        return $this->morphTo();
    }


    /**
     * Get the cohort that owns the Sale
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cohort(): BelongsTo
    {
        return $this->belongsTo(Cohort::class);
    }

    /**
     * Get the saleType that owns the Sale
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(SaleType::class, 'sale_type_id');
    }

    /**
     * Get the referral associated with the Sale
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function referral(): HasOne
    {
        return $this->hasOne(Referral::class);
    }


    public static function get_all() {
        return DB::select('select sales.id, sales.price, sales.date, concat(students.first_name, " ", students.last_name) as student from sales inner join students on sales.student_id = students.id order by sales.date desc');
    }


    public static function get_sale(int $id) {

        $sale = static::where('id', $id)->first();

        
        if (!$sale) return null;

        $sale_type = $sale->type->name;
        
        $buyer['name'] = $sale->student->name;
        
        $buyer['email'] = $sale->student->email;

        if ($sale_type == 'Cohort') {
            $cohort['name'] = $sale->cohort->name;

        } else {
            $bought_course = $sale->course;
            
            if ($bought_course instanceof CertificateCourse) {
                $course['type'] = 'Certificate Course';
                $course['name'] = $bought_course->title.' - '.$bought_course->code;
            } else if ($bought_course instanceof CertificationCourse) {
                $course['type'] = 'Certification Course';
                $course['name'] = $bought_course->title.' - '.$bought_course->code;
            } else if ($bought_course instanceof OffshoreCourse) {
                $course['type'] = 'Certificate Course';
                $course['name'] = $bought_course->title;
            }
        }
        
        
        // var_dump($course); return null;

        $referral = $sale->referral;

        $affiliate = null;

        if ($referral) {


            $affiliate['commission'] = $referral->commission;
    
            $referrer = $referral->referrer;
    
            $affiliate['name'] = $referrer->name;
    
            $affiliate['email'] = $referrer->email;
        }

        $data = ['type'=>$sale_type, 'student'=>$buyer, 'price'=>$sale->price, 'date'=>$sale->date, 'affiliate'=>$affiliate];

        if ($sale_type == 'Cohort') $data = [...$data, 'cohort'=>$cohort];
        else if ($sale_type == 'Individual Course') $data = [...$data, 'course'=>$course];



        return $data;
    }
    
}
