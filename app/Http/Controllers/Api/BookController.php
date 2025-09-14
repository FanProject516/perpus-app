<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class BookController extends Controller
{
    /**
     * Display a listing of books
     */
    public function index(Request $request): JsonResponse
    {
        $query = Book::with(['category']);

        // Search functionality
        if ($request->has('search')) {
            $query->search($request->search);
        }

        // Filter by category
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by availability
        if ($request->has('available') && $request->available) {
            $query->available();
        }

        // Filter by language
        if ($request->has('language')) {
            $query->where('language', $request->language);
        }

        // Sort options
        $sortBy = $request->get('sort_by', 'title');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->get('per_page', 15);
        $books = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $books
        ]);
    }

    /**
     * Store a newly created book
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'isbn' => 'nullable|string|unique:books|max:20',
            'publisher' => 'nullable|string|max:255',
            'year' => 'nullable|integer|min:1000|max:' . date('Y'),
            'category_id' => 'required|exists:categories,id',
            'summary' => 'nullable|string',
            'cover' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'total_copies' => 'required|integer|min:1',
            'price' => 'nullable|numeric|min:0',
            'language' => 'nullable|string|max:50',
            'pages' => 'nullable|integer|min:1',
            'condition' => 'nullable|in:new,good,fair,poor',
            'location' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->except('cover');
        $data['available_copies'] = $request->total_copies;
        $data['is_available'] = true;

        // Handle cover upload
        if ($request->hasFile('cover')) {
            $coverPath = $request->file('cover')->store('book-covers', 'public');
            $data['cover_path'] = $coverPath;
        }

        $book = Book::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Book created successfully',
            'data' => $book->load('category')
        ], 201);
    }

    /**
     * Display the specified book
     */
    public function show(Book $book): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $book->load(['category', 'copies'])
        ]);
    }

    /**
     * Update the specified book
     */
    public function update(Request $request, Book $book): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'author' => 'sometimes|string|max:255',
            'isbn' => 'sometimes|nullable|string|max:20|unique:books,isbn,' . $book->id,
            'publisher' => 'sometimes|nullable|string|max:255',
            'year' => 'sometimes|nullable|integer|min:1000|max:' . date('Y'),
            'category_id' => 'sometimes|exists:categories,id',
            'summary' => 'sometimes|nullable|string',
            'cover' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'total_copies' => 'sometimes|integer|min:1',
            'price' => 'sometimes|nullable|numeric|min:0',
            'language' => 'sometimes|nullable|string|max:50',
            'pages' => 'sometimes|nullable|integer|min:1',
            'condition' => 'sometimes|nullable|in:new,good,fair,poor',
            'location' => 'sometimes|nullable|string|max:100',
            'is_available' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->except('cover');

        // Handle cover upload
        if ($request->hasFile('cover')) {
            // Delete old cover if exists
            if ($book->cover_path) {
                Storage::disk('public')->delete($book->cover_path);
            }
            
            $coverPath = $request->file('cover')->store('book-covers', 'public');
            $data['cover_path'] = $coverPath;
        }

        // Adjust available copies if total copies changed
        if ($request->has('total_copies')) {
            $borrowedCopies = $book->total_copies - $book->available_copies;
            $data['available_copies'] = max(0, $request->total_copies - $borrowedCopies);
        }

        $book->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Book updated successfully',
            'data' => $book->load('category')
        ]);
    }

    /**
     * Remove the specified book
     */
    public function destroy(Book $book): JsonResponse
    {
        // Check if book has active loans
        if ($book->loans()->whereIn('status', ['borrowed', 'overdue'])->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete book with active loans'
            ], 422);
        }

        // Delete cover image if exists
        if ($book->cover_path) {
            Storage::disk('public')->delete($book->cover_path);
        }

        $book->delete();

        return response()->json([
            'success' => true,
            'message' => 'Book deleted successfully'
        ]);
    }

    /**
     * Get available books for borrowing
     */
    public function available(Request $request): JsonResponse
    {
        $query = Book::available()->with(['category']);

        // Search functionality
        if ($request->has('search')) {
            $query->search($request->search);
        }

        // Filter by category
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $perPage = $request->get('per_page', 15);
        $books = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $books
        ]);
    }

    /**
     * Get book statistics
     */
    public function statistics(): JsonResponse
    {
        $stats = [
            'total_books' => Book::count(),
            'available_books' => Book::available()->count(),
            'borrowed_books' => Book::whereColumn('available_copies', '<', 'total_copies')->count(),
            'books_by_category' => Book::join('categories', 'books.category_id', '=', 'categories.id')
                                     ->selectRaw('categories.name, COUNT(*) as count')
                                     ->groupBy('categories.name')
                                     ->get(),
            'most_borrowed' => Book::withCount(['loans' => function ($query) {
                                       $query->where('status', 'returned');
                                   }])
                                   ->orderBy('loans_count', 'desc')
                                   ->limit(10)
                                   ->get(['title', 'author', 'loans_count'])
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}
