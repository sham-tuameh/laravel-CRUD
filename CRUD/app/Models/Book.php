<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
    use HasFactory;

    public $fillable = [
        'user_id',
        'book_name',
        'book_cover_url',
        'book_pdf',
        'published_date',
        'description',
        'have_rating_over_4'
    ];

    public function user(): HasMany
    {
        return $this->hasMany(User::class);
    }


}
