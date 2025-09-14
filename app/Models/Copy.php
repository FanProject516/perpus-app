<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Copy extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_id',
        'barcode',
        'condition',
        'location',
        'is_available',
        'notes'
    ];

    protected function casts(): array
    {
        return [
            'is_available' => 'boolean',
        ];
    }

    /**
     * Get the book that owns the copy.
     */
    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    /**
     * Get the loans for the copy.
     */
    public function loans()
    {
        return $this->hasMany(Loan::class);
    }

    /**
     * Get the current active loan for the copy.
     */
    public function currentLoan()
    {
        return $this->hasOne(Loan::class)->whereIn('status', ['borrowed', 'overdue']);
    }

    /**
     * Scope a query to only include available copies.
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    /**
     * Scope a query to only include borrowed copies.
     */
    public function scopeBorrowed($query)
    {
        return $query->where('is_available', false);
    }

    /**
     * Check if copy is currently borrowed.
     */
    public function isBorrowed(): bool
    {
        return !$this->is_available && $this->currentLoan()->exists();
    }

    /**
     * Mark copy as borrowed.
     */
    public function markAsBorrowed()
    {
        $this->update(['is_available' => false]);
    }

    /**
     * Mark copy as returned.
     */
    public function markAsReturned()
    {
        $this->update(['is_available' => true]);
    }
}
