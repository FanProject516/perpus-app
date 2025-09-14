<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBookRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->hasRole(['librarian', 'admin']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $bookId = $this->route('book')->id ?? null;
        
        return [
            'title' => 'sometimes|string|max:255',
            'author' => 'sometimes|string|max:255',
            'isbn' => 'sometimes|nullable|string|max:20|unique:books,isbn,' . $bookId,
            'publisher' => 'sometimes|nullable|string|max:255',
            'year' => 'sometimes|nullable|integer|min:1000|max:' . date('Y'),
            'category_id' => 'sometimes|exists:categories,id',
            'summary' => 'sometimes|nullable|string|max:2000',
            'cover' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'total_copies' => 'sometimes|integer|min:1|max:1000',
            'price' => 'sometimes|nullable|numeric|min:0|max:99999999.99',
            'language' => 'sometimes|nullable|string|max:50',
            'pages' => 'sometimes|nullable|integer|min:1|max:10000',
            'condition' => 'sometimes|nullable|in:new,good,fair,poor',
            'location' => 'sometimes|nullable|string|max:100',
            'is_available' => 'sometimes|boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'isbn.unique' => 'This ISBN already exists in the system.',
            'category_id.exists' => 'Selected category does not exist.',
            'total_copies.min' => 'At least one copy is required.',
            'total_copies.max' => 'Maximum 1000 copies allowed.',
            'cover.image' => 'Cover must be an image file.',
            'cover.max' => 'Cover image size must not exceed 2MB.',
            'year.min' => 'Year must be at least 1000.',
            'year.max' => 'Year cannot be in the future.',
            'price.min' => 'Price cannot be negative.',
            'pages.min' => 'Pages must be at least 1.',
            'pages.max' => 'Pages cannot exceed 10,000.',
        ];
    }
}
