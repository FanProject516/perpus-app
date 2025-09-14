<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Loan extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'book_id',
        'copy_id',
        'borrowed_at',
        'due_date',
        'returned_at',
        'status',
        'fine_amount',
        'notes'
    ];

    protected function casts(): array
    {
        return [
            'borrowed_at' => 'datetime',
            'due_date' => 'datetime',
            'returned_at' => 'datetime',
            'fine_amount' => 'decimal:2',
        ];
    }

    /**
     * Get the user that owns the loan.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the book that owns the loan.
     */
    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    /**
     * Get the copy that owns the loan.
     */
    public function copy()
    {
        return $this->belongsTo(Copy::class);
    }

    /**
     * Scope a query to only include active loans.
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['borrowed', 'overdue']);
    }

    /**
     * Scope a query to only include overdue loans.
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', 'overdue')
                    ->orWhere(function ($q) {
                        $q->where('status', 'borrowed')
                          ->where('due_date', '<', now());
                    });
    }

    /**
     * Scope a query to only include returned loans.
     */
    public function scopeReturned($query)
    {
        return $query->where('status', 'returned');
    }

    /**
     * Check if the loan is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->status === 'overdue' || 
               ($this->status === 'borrowed' && $this->due_date < now());
    }

    /**
     * Get the number of days overdue.
     */
    public function getDaysOverdue(): int
    {
        if (!$this->isOverdue()) {
            return 0;
        }

        return now()->diffInDays($this->due_date);
    }

    /**
     * Calculate fine amount based on days overdue.
     */
    public function calculateFine(float $dailyFineRate = 1000): float
    {
        $daysOverdue = $this->getDaysOverdue();
        return $daysOverdue * $dailyFineRate;
    }

    /**
     * Mark loan as returned.
     */
    public function markAsReturned()
    {
        $this->update([
            'status' => 'returned',
            'returned_at' => now(),
            'fine_amount' => $this->calculateFine()
        ]);

        // Mark copy as available
        if ($this->copy) {
            $this->copy->markAsReturned();
        }

        // Increase available copies for book
        if ($this->book) {
            $this->book->increaseAvailableCopies();
        }
    }

    /**
     * Mark loan as overdue.
     */
    public function markAsOverdue()
    {
        if ($this->status === 'borrowed' && $this->due_date < now()) {
            $this->update([
                'status' => 'overdue',
                'fine_amount' => $this->calculateFine()
            ]);
        }
    }

    /**
     * Extend due date.
     */
    public function extendDueDate(int $days)
    {
        $this->update([
            'due_date' => $this->due_date->addDays($days)
        ]);
    }
}
