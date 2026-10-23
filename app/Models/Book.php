<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Book extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'author',
        'isbn',
        'category',
        'publisher',
        'published_year',
        'total_copies',
        'available_copies',
    ];

    /**
     * The borrowing history of this book.
     */
    public function borrowings(): HasMany
    {
        return $this->hasMany(Borrowing::class);
    }
}