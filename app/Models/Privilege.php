<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Privilege extends Model
{
    use HasFactory;

    public $timestamps = false;
    public $guarded = ['id'];
    protected $hidden = ['id'];
    

    /**
     * The admins that belong to the Privilege
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function admins(): BelongsToMany
    {
        return $this->belongsToMany(Admin::class);
    }
}
