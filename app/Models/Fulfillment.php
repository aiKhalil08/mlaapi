<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class Fulfillment extends Model
{
    use HasFactory;

    public $timestamps = false;
    public $guarded = ['id'];
    // protected $hidden = ['id'];


    /**
     * Get the student that owns the Fulfillment
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }


    public static function get_all() {
        $pending = DB::select('select fulfillments.id, fulfillments.amount, fulfillments.date_added, fulfillments.type, concat(students.first_name, " ", students.last_name) as student from fulfillments inner join students on fulfillments.student_id = students.id and fulfillments.status = 0 order by fulfillments.date_added desc');

        $history = DB::select('select fulfillments.id, fulfillments.amount, fulfillments.date_fulfilled, fulfillments.status, fulfillments.type, concat(students.first_name, " ", students.last_name) as student from fulfillments inner join students on fulfillments.student_id = students.id and fulfillments.status <> 0 order by fulfillments.date_fulfilled desc');


        return ['pending' => $pending, 'history'=>$history];
    }


    public static function get(int $id) {
        $fulfillment = DB::select('select fulfillments.amount, fulfillments.date_added, fulfillments.type, fulfillments.account_details, concat(students.first_name, " ", students.last_name) as name, students.email from fulfillments inner join students on fulfillments.student_id = students.id and fulfillments.id = ?', [$id]);



        return $fulfillment[0];
    }
}
