<?php

namespace App\Http\Requests;

use App\Models\Employee;
use Illuminate\Foundation\Http\FormRequest;

class formEmployee extends FormRequest
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
        $employeeId = $this->route('id');
        $isUpdate = $employeeId !== '-1';
        $userId = $isUpdate ? $this->getEmployeeUserId() : 0;


        return [
            'name' => ['required', 'string', 'min:3', 'max:100', 'regex:/^[a-zA-Z\s]+$/'],
            'last_name' => ['required', 'string', 'min:5', 'max:100', 'regex:/^[a-zA-Z\s]+$/'],
            'department_id' => ['required', 'integer', 'exists:departments,id'],
            'user' => [
                'required',
                'string',
                'min:5',
                'max:100',
                'regex:/^[a-zA-Z\s]+$/',
                $isUpdate ? 'unique:users,name,' . $userId : 'unique:users,name' // Unique User
            ],
            'password' => [
                $isUpdate ? 'required_if:password,!=,null' : 'required', // Required if update is true
                'string',
                'min:8',
                'regex:/[a-z]/', // At least one lowercase letter
                'regex:/[A-Z]/', // At least one uppercase letter
                'regex:/[0-9]/', // At least one number
                'regex:/[@$!%*#?&]/' // At least one special character
            ]
        ];
    }

    /**
     * Get the user ID associated with the employee.
     */
    public function getEmployeeUserId(): int
    {
        $employeeId = $this->route('id');
        $employee = Employee::find($employeeId);
        return $employee ? $employee->user_id : 0;
    }
}
