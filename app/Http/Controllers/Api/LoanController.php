<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Copy;
use App\Models\Loan;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LoanController extends Controller
{
    /**
     * Display a listing of loans
     */
    public function index(Request $request): JsonResponse
    {
        $query = Loan::with(['user', 'book', 'copy']);

        // Filter by user (for members to see their own loans)
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by overdue
        if ($request->has('overdue') && $request->overdue) {
            $query->overdue();
        }

        // Filter by date range
        if ($request->has('from_date')) {
            $query->whereDate('borrowed_at', '>=', $request->from_date);
        }

        if ($request->has('to_date')) {
            $query->whereDate('borrowed_at', '<=', $request->to_date);
        }

        // Sort options
        $sortBy = $request->get('sort_by', 'borrowed_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->get('per_page', 15);
        $loans = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $loans
        ]);
    }

    /**
     * Create a new loan (borrow book)
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'book_id' => 'required|exists:books,id',
            'due_date' => 'nullable|date|after:today',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $book = Book::findOrFail($request->book_id);

        // Check if user has active loans limit
        $activeLoanCount = $user->activeLoans()->count();
        $maxLoans = $user->hasRole('librarian') ? 10 : 3; // Librarians can borrow more

        if ($activeLoanCount >= $maxLoans) {
            return response()->json([
                'success' => false,
                'message' => "Maximum loan limit ($maxLoans) reached"
            ], 422);
        }

        // Check if book is available
        if (!$book->isAvailableForLoan()) {
            return response()->json([
                'success' => false,
                'message' => 'Book is not available for loan'
            ], 422);
        }

        // Check if user already has this book on loan
        if ($user->activeLoans()->where('book_id', $book->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'You already have this book on loan'
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Find available copy
            $copy = $book->copies()->available()->first();
            if (!$copy) {
                // Create a virtual copy if none exists
                $copy = Copy::create([
                    'book_id' => $book->id,
                    'barcode' => 'AUTO-' . $book->id . '-' . time(),
                    'condition' => 'good',
                    'location' => $book->location ?? 'Main Library',
                    'is_available' => false
                ]);
            } else {
                $copy->markAsBorrowed();
            }

            // Create loan
            $dueDate = $request->due_date ? 
                      Carbon::parse($request->due_date) : 
                      now()->addDays(14); // Default 2 weeks

            $loan = Loan::create([
                'user_id' => $user->id,
                'book_id' => $book->id,
                'copy_id' => $copy->id,
                'borrowed_at' => now(),
                'due_date' => $dueDate,
                'status' => 'borrowed'
            ]);

            // Update book availability
            $book->decreaseAvailableCopies();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Book borrowed successfully',
                'data' => $loan->load(['user', 'book', 'copy'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to borrow book: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Return a book
     */
    public function returnBook(Request $request, Loan $loan): JsonResponse
    {
        if ($loan->status === 'returned') {
            return response()->json([
                'success' => false,
                'message' => 'Book is already returned'
            ], 422);
        }

        if ($loan->status !== 'borrowed' && $loan->status !== 'overdue') {
            return response()->json([
                'success' => false,
                'message' => 'Invalid loan status for return'
            ], 422);
        }

        DB::beginTransaction();
        try {
            $loan->markAsReturned();
            
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Book returned successfully',
                'data' => [
                    'loan' => $loan->fresh()->load(['user', 'book', 'copy']),
                    'fine_amount' => $loan->fine_amount
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to return book: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Extend loan due date
     */
    public function extend(Request $request, Loan $loan): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'days' => 'required|integer|min:1|max:14',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        if ($loan->status !== 'borrowed') {
            return response()->json([
                'success' => false,
                'message' => 'Can only extend active borrowed books'
            ], 422);
        }

        // Check if loan can be extended (not overdue)
        if ($loan->isOverdue()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot extend overdue loans'
            ], 422);
        }

        $loan->extendDueDate($request->days);

        return response()->json([
            'success' => true,
            'message' => 'Loan extended successfully',
            'data' => $loan->fresh()
        ]);
    }

    /**
     * Get user's active loans
     */
    public function myLoans(Request $request): JsonResponse
    {
        $query = $request->user()->loans()->with(['book', 'copy']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        } else {
            // Default to active loans
            $query->active();
        }

        $loans = $query->orderBy('borrowed_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $loans
        ]);
    }

    /**
     * Get overdue loans
     */
    public function overdue(Request $request): JsonResponse
    {
        $query = Loan::overdue()->with(['user', 'book', 'copy']);

        $perPage = $request->get('per_page', 15);
        $loans = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $loans
        ]);
    }

    /**
     * Mark loans as overdue (scheduled task)
     */
    public function markOverdue(): JsonResponse
    {
        $overdueLoans = Loan::where('status', 'borrowed')
                           ->where('due_date', '<', now())
                           ->get();

        foreach ($overdueLoans as $loan) {
            $loan->markAsOverdue();
        }

        return response()->json([
            'success' => true,
            'message' => "Marked {$overdueLoans->count()} loans as overdue"
        ]);
    }

    /**
     * Get loan statistics
     */
    public function statistics(): JsonResponse
    {
        $stats = [
            'total_loans' => Loan::count(),
            'active_loans' => Loan::active()->count(),
            'overdue_loans' => Loan::overdue()->count(),
            'returned_loans' => Loan::returned()->count(),
            'total_fines' => Loan::sum('fine_amount'),
            'loans_by_month' => Loan::selectRaw('MONTH(borrowed_at) as month, COUNT(*) as count')
                                   ->whereYear('borrowed_at', date('Y'))
                                   ->groupBy('month')
                                   ->get(),
            'most_active_borrowers' => Loan::join('users', 'loans.user_id', '=', 'users.id')
                                          ->selectRaw('users.name, COUNT(*) as loan_count')
                                          ->groupBy('users.id', 'users.name')
                                          ->orderBy('loan_count', 'desc')
                                          ->limit(10)
                                          ->get()
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Get loan details
     */
    public function show(Loan $loan): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $loan->load(['user', 'book', 'copy'])
        ]);
    }
}
