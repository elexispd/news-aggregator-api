<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Category;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'description',
        'category_id',
        'source_id',
        'author',
        'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function category()
    {
        return $this->belongsTo(Categories::class);
    }

    public function source()
    {
        return $this->belongsTo(Source::class);
    }


    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
