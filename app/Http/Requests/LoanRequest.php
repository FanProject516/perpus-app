<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->is_active;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'book_id' => 'required|exists:books,id',
            'due_date' => 'nullable|date|after:today|before:' . now()->addMonths(3)->format('Y-m-d'),
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'book_id.required' => 'Please select a book to borrow.',
            'book_id.exists' => 'Selected book does not exist.',
            'due_date.after' => 'Due date must be in the future.',
            'due_date.before' => 'Due date cannot be more than 3 months from now.',
        ];
    }
}
