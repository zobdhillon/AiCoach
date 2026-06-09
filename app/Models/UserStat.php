<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserStat extends Model
{
    protected $fillable = ['user_id', 'best_streak'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
