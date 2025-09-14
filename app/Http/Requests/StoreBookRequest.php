<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookRequest extends FormRequest
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
        return [
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'isbn' => 'nullable|string|unique:books|max:20',
            'publisher' => 'nullable|string|max:255',
            'year' => 'nullable|integer|min:1000|max:' . date('Y'),
            'category_id' => 'required|exists:categories,id',
            'summary' => 'nullable|string|max:2000',
            'cover' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'total_copies' => 'required|integer|min:1|max:1000',
            'price' => 'nullable|numeric|min:0|max:99999999.99',
            'language' => 'nullable|string|max:50',
            'pages' => 'nullable|integer|min:1|max:10000',
            'condition' => 'nullable|in:new,good,fair,poor',
            'location' => 'nullable|string|max:100',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Book title is required.',
            'author.required' => 'Author name is required.',
            'isbn.unique' => 'This ISBN already exists in the system.',
            'category_id.required' => 'Please select a category.',
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
