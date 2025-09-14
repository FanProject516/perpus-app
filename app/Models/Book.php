<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'author',
        'isbn',
        'publisher',
        'year',
        'category_id',
        'summary',
        'cover_path',
        'total_copies',
        'available_copies',
        'price',
        'language',
        'pages',
        'condition',
        'location',
        'is_available'
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'total_copies' => 'integer',
            'available_copies' => 'integer',
            'price' => 'decimal:2',
            'pages' => 'integer',
            'is_available' => 'boolean',
        ];
    }

    /**
     * Get the category that owns the book.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the copies for the book.
     */
    public function copies()
    {
        return $this->hasMany(Copy::class);
    }

    /**
     * Get the loans for the book.
     */
    public function loans()
    {
        return $this->hasMany(Loan::class);
    }

    /**
     * Scope a query to only include available books.
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true)->where('available_copies', '>', 0);
    }

    /**
     * Scope a query to search books by title, author, or ISBN.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('author', 'like', "%{$search}%")
              ->orWhere('isbn', 'like', "%{$search}%");
        });
    }

    /**
     * Check if book is available for loan.
     */
    public function isAvailableForLoan(): bool
    {
        return $this->is_available && $this->available_copies > 0;
    }

    /**
     * Decrease available copies when borrowed.
     */
    public function decreaseAvailableCopies()
    {
        if ($this->available_copies > 0) {
            $this->decrement('available_copies');
        }
    }

    /**
     * Increase available copies when returned.
     */
    public function increaseAvailableCopies()
    {
        if ($this->available_copies < $this->total_copies) {
            $this->increment('available_copies');
        }
    }
}
