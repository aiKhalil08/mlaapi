<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Company extends Model
{
    use HasFactory;

    public $timestamps = false;
    public $guarded = ['id'];
    protected $hidden = ['id', 'pivot'];

    /**
     * The staff that belong to the Company
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function staff(): BelongsToMany
    {
        return $this->belongsToMany(ExternalUser::class, 'company_external_user', 'company_id', 'user_id');
    }
}
