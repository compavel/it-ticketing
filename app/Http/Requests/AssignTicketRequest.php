<?php

namespace App\Http\Requests;

use App\UserRole;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignTicketRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Hanya admin (dicek TicketPolicy::assign via Gate).
     */
    public function authorize(): bool
    {
        return $this->user()?->can('assign', $this->route('ticket')) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // ID harus user dengan role technician/admin — user biasa tidak
            // valid sebagai penanggung jawab ticket.
            'technician_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')
                    ->where(fn ($query) => $query->where('role', '!=', UserRole::User->value)),
            ],
        ];
    }
}
