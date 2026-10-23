<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Borrowing extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'book_id',
        'member_id',
        'borrowed_at',
        'due_at',
        'returned_at',
    ];

    /**
     * The book associated with this borrowing.
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class)->withTrashed();
    }

    /**
     * The member associated with this borrowing.
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class)->withTrashed();
    }
}