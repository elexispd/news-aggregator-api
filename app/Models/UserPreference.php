<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class UserPreference extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'categories', 'sources', 'authors'];

    protected $casts = [
        'categories' => 'array',
        'sources' => 'array',
        'authors' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
