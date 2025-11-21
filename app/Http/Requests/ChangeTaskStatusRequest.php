<?php

namespace App\Http\Requests;

use App\Models\Task;
use App\Enums\TaskStatus;
use Illuminate\Validation\Rule;
use App\Rules\OnlyManagerCanCancelTask;
use Illuminate\Foundation\Http\FormRequest;

class ChangeTaskStatusRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {

        return [
            'status' => [
                'required',
                'string',
                Rule::in(array_column([TaskStatus::PENDING, TaskStatus::IN_PROGRESS, TaskStatus::COMPLETED, TaskStatus::CANCELLED], 'value')),
                new OnlyManagerCanCancelTask($this->user()),
            ],
        ];
    }
}
