<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Http\Controllers\Controller;
use App\Http\Requests\formEmployee;
use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use App\Traits\ApiResponse;
use ArgumentCountError;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

define('TABLE_EMPLOYEES', 'employees');
define('TABLE_HISTORY_ACCESS', 'history_accesses');

class EmployeeController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of the resource.
     * @return View
     */
    public function index()
    {
        // Get All Employees
        $employees = Employee::withCount('historyAccess')->paginate(10);

        // Get All Departments
        $departments = Department::all();
        // Get All Roles
        $roles = Role::all();

        return view('main-panel', compact('departments', 'employees', 'roles'));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return array{success: bool, message: string, data: Employee|null}
     */
    public function store(formEmployee $request)
    {
        try {
            // Validate Request
            $validated = $request->validated();

            // Create User
            $user = User::create([
                'name' => $validated['user'],
                'password' => bcrypt($validated['password']),
                'role_id' => $request['role_id'] == 0 ? null : $request['role_id']
            ]);

            // Create Employee
            $employee = Employee::create([
                'name' => $validated['name'],
                'last_name' => $validated['last_name'],
                'department_id' => $validated['department_id'],
                'user_id' => $user->id,
            ]);

            // Return Employee
            return $this->response(true, "Employee created successfully", 201, $employee);
        } catch (ValidationException $e) {
            return $this->response(false, "Error validate data employee: " . $e->getMessage(), 422);
        } catch (\Exception $e) {
            return $this->response(false, "Error creating employee: " . $e->getMessage(), 500);
        }
    }

    /**
     * Store employees for csv file
     * @param Request $request
     * @return array{success: bool, message: string, data: Employee[]|null}
     */
    public function storeEmployees(Request $request)
    {
        try {
            // Validate Request
            $request->validate([
                'import' => 'required|mimes:csv,txt',
            ]);

            // Open File
            if (!$file = fopen($request->file('import'), 'r')) return $this->response(false, "Error opening file", 500);

            // Open file like raw text to detect the delimiter
            $rawLine = fgets($file);

            // Detect delimiter
            $delimiter = (substr_count($rawLine, ';') > substr_count($rawLine, ',')) ? ';' : ',';

            // Return to the beginning of the file
            rewind($file);

            // Read and clean the Header
            $header = array_map(function ($item) {
                return strtolower(trim(preg_replace('/\x{FEFF}/u', '', $item))); // Remove BOM
            }, fgetcsv($file, 0, $delimiter));

            $headerRequired = ['user', 'password', 'name', 'last_name', 'department', 'active'];

            $missing = array_diff($headerRequired, $header);
            if (!empty($missing)) {
                return $this->response(false, "The file must contain the following columns: " . implode(', ', $headerRequired), 422);
            }

            // Start Transaction
            DB::beginTransaction();

            while (($row = fgetcsv($file, 0, $delimiter)) !== false) {

                // Basic cleaning of each cell in the row
                $row = array_map(function ($item) {
                    return trim($item);
                }, $row);

                // Check if the number of columns matches the header
                if (count($header) !== count($row)) {
                    return $this->response(false, "CSV format issue: columns do not match header on row: " . implode(', ', $row), 422);
                }

                $data = array_combine($header, $row); // Combine Header with Row

                // Validate Data
                $validated = Validator::make($data, [
                    'user' => ['required', 'string', 'min:5', 'max:100', 'regex:/^[a-zA-Z0-9]+$/'],
                    'password' => [
                        'required',
                        'string',
                        'min:8',
                        'regex:/[a-z]/',
                        'regex:/[A-Z]/',
                        'regex:/[0-9]/',
                        'regex:/[@$!%*#?&]/'
                    ],
                    'name' => ['required', 'string', 'min:3', 'max:100', 'regex:/^[a-zA-Z\s]+$/'],
                    'last_name' => ['required', 'string', 'min:3', 'max:100', 'regex:/^[a-zA-Z\s]+$/'],
                    'department' => ['required', 'string', 'min:3', 'max:100', 'regex:/^[a-zA-Z0-9\s&\-\.]+$/'],
                    'active' => ['required', 'integer', 'in:0,1']
                ])->validate();

                // Get or create department
                $department = $this->getOrCreateDepartment($validated['department']);

                $role = Role::where('name', 'role_admin_room_911')->first(); // Get Role

                // Create or update user
                $user = User::updateOrCreate(
                    ['name' => $validated['user']],
                    [
                        'password' => bcrypt($validated['password']),
                        'active' => $validated['active'],
                        'role_id' => $role->id // Assign role
                    ]
                );

                // Create or update employee
                Employee::updateOrCreate(
                    [
                        'name' => $validated['name'],
                        'last_name' => $validated['last_name']
                    ],
                    [
                        'user_id' => $user->id,
                        'department_id' => $department->id
                    ]
                );
            }
            fclose($file); // Close File

            DB::commit(); // All good, commit changes

            return $this->response(true, "Employees imported successfully", 201);
        } catch (\Exception $e) {
            DB::rollBack(); // Something went wrong, rollback changes
            return $this->response(false, "Error importing employees: " . $e->getMessage(), 500);
        }
    }

    /**
     * Get the specified employee by id.
     * @param int $idEmployee
     * @return array{success: bool, message: string,data: Employee|null}
     */
    public function show(int $idEmployee)
    {
        try {
            // Find Employee
            $employee = Employee::with('user')->find($idEmployee);

            // Return Employee
            return $this->response(true, "Employee information obtained successfully", 200, $employee);
        } catch (\Exception $e) {
            return $this->response(false, "Error obtaining employee information: " . $e->getMessage(), 500);
        }
    }

    /**
     * Show a list of employees according to established parameters.
     * Export to PDF
     * @param Request $request
     * @return array{success: bool, message: string, data: Employee[]|null}
     */
    public function showByParameters(Request $request)
    {
        // Get Export Value from Request (0 or 1)
        $export = $request->input('export') == 1;

        try {
            // Create Query
            $query = Employee::query()
                ->select(TABLE_EMPLOYEES . '.*')
                ->leftjoin(TABLE_HISTORY_ACCESS, 'employees.id', '=', TABLE_HISTORY_ACCESS . '.employee_id')
                ->distinct(TABLE_EMPLOYEES .  '.id')
                ->with('department')
                ->withCount('historyAccess'); // Count Access

            // Get Parameters
            $employee    = $request->filled('employee') ? $request->employee : null;
            $department  = $request->filled('department') ? $request->department : null;
            $initial     = $request->filled('initial') ? $request->initial : null;
            $final       = $request->filled('final') ? $request->final : null;

            // Validate Parameters
            if ($employee) {
                if (is_numeric($employee)) {
                    $query->where(TABLE_EMPLOYEES . '.id', $employee);
                } else {
                    $query->where(function ($query) use ($employee) {
                        $query->where('name', 'like', '%' . $employee . '%')
                              ->orWhere('last_name', 'like', '%' . $employee . '%');
                    });
                }
            }

            if ($department) $query->where('department_id', $department);

            if ($initial && $final) {
                // Add start and end times to the dates
                $initial = $initial . ' 00:00:00';
                $final = $final . ' 23:59:59';
                $query->whereBetween(TABLE_HISTORY_ACCESS . '.created_at', [$initial, $final]);
            } else if ($initial) {
                $query->where(TABLE_HISTORY_ACCESS . '.created_at', '>=', $initial);
            } else if ($final) {
                $query->where(TABLE_HISTORY_ACCESS . '.created_at', '<=', $final);
            }

            $employees = $export ? $query->get() : $query->paginate(10); // Get Employees

            if ($export) {
                // Set Date
                $date = date('Y-m-d H:i:s');
                $title = ($initial && $final) ? "Employee Report From " . $initial . " To " . $final   : "Employees Report";
                // Generate PDF
                $pdf = PDF::loadView('pdf.employee', compact('employees', 'date', 'title'));
                return $pdf->download('employees_' . $date . '.pdf');
            }

            // Return Employees
            return $this->response(true, "Employees obtained successfully", 200, $employees);
        } catch (\Exception $e) {
            return $this->response(false, "Error obtaining employee information: " . $e->getMessage(), 500);
        }
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @return array{success: bool, message: string, data: Employee|null}
     */
    public function update(formEmployee $request)
    {
        $user = null;
        try {
            // Validate Request
            $validated = $request->validated();

            // Find Employee
            $employee = Employee::find($request->employee_id);
            // Find User
            $user = User::find($employee->user_id);

            // Update User
            $user->name = $validated['user'];
            if ($request->password) {
                $user->password = bcrypt($validated['password']);
            }
            $user->role_id = $request['role_id'] == 0 ? null : $request['role_id'];
            $user->save();

            // Update Employee
            $employee->name = $validated['name'];
            $employee->last_name = $validated['last_name'];
            $employee->department_id = $validated['department_id'];
            $employee->save();

            // Return Employee
            return $this->response(true, "Employee updated successfully", 200, $employee);
        } catch (ValidationException $e) {
            return $this->response(false, "Error validate data employee: " . $e->getMessage(), 422);
        } catch (\Exception $e) {
            return $this->response(false, "Error updating employee: " . $e->getMessage(), 500, $user);
        }
    }

    /**
     * Delete employee and user
     * @param int $idEmployee
     * @param int $idUser
     * @return array{success: bool, message: string, data: null}
     */
    public function destroy(int $idEmployee, int $idUser)
    {
        try {
            // Find Employee
            $employee = Employee::find($idEmployee);
            // Find User
            $user = User::find($idUser);

            // Delete Employee
            $employee->delete();
            // Delete User
            $user->delete();

            return $this->response(true, "Employee deleted successfully", 200);
        } catch (\Exception $e) {
            return $this->response(false, "Error deleting employee: " . $e->getMessage(), 500);
        }
    }

    /**
     * Validate form request
     * @param Request $request
     * @return JsonResponse
     */
    public function validatorForm(formEmployee $request)
    {
        try {
            // Validate Request
            $request->validate();
            return response()->json(['message' => 'Valid!'], 200); // Return Valid
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422); // Return Errors
        } catch (ArgumentCountError $e) {
            return response()->json(['message' => 'Valid!'], 200); // Return Valid
        }
    }

    /**
     * Normalizes the name of the department and obtains your ID
     * @param string $name
     * @return Department
     * @throws \Exception
     */
    private function getOrCreateDepartment(string $name)
    {
        // Normalize name
        $normalizedName = strtolower(trim($name));

        // Search for the department
        $department = Department::whereRaw('LOWER(TRIM(name)) = ?', [$normalizedName])->first();

        // If it does not exist, create it
        if (!$department) {
            try {
                $department = Department::create([
                    'name' => ucfirst($normalizedName),
                    'description' => 'Department created for employee import'
                ]);
            } catch (QueryException $e) {
                // If a career between processes occurs and another already created it
                $department = Department::whereRaw('LOWER(TRIM(name)) = ?', [$normalizedName])->first();
            }
        }

        if (!$department || !$department->id) {
            throw new \Exception("The department could not be obtained or created: " . $name);
        }
        return $department;
    }
}
