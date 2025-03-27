<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\HistoryAccess;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{

    use ApiResponse;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('login');
    }

    /**
     * Login the user.
     * @param Request $request
     * @return Redirector|RedirectResponse
     */
    public function login(Request $request)
    {
        try {
            // Validate the request
            $validated = $request->validate([
                'name' => 'required|string',
                'password' => 'required',
            ]);

            // Search the user
            $user = User::where('name', $validated['name'])->first();

            // Check if the user exists
            if (!$user) return $this->createHistoryAccess(null, null, false, 'User not found');

            $id = $user->employee->id; // Get the employee id
            $name_complete = $user->employee->name . ' ' . $user->employee->last_name; // Get the complete name

            // Check if the user is active
            if (!$user->active) return $this->createHistoryAccess($id, $name_complete, false, 'User not permitted');

            if (Auth::attempt($validated)) {
                $request->session()->regenerate();
                return $this->createHistoryAccess($id, $name_complete, true, 'Access granted');
            }
            return $this->createHistoryAccess($id, $name_complete, false, 'Invalid password');
        } catch (\Exception $e) {
            return redirect()->route('login')->with('error', 'Invalid user or password: ' . $e->getMessage());
        }
    }

    /**
     * Logout the user.
     * @param Request $request
     * @return Redirector
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }


    /**
     * Create history access.
     * @param $employee_id
     * @param $employee_name_complete
     * @param $success
     * @param $reason
     * @return Redirector|RedirectResponse
     */
    public function createHistoryAccess($employee_id, $employee_name_complete, $success, $reason)
    {
        try {
            // Create history access
            HistoryAccess::create([
                'employee_id' => $employee_id,
                'employee_name_complete' => $employee_name_complete,
                'success' => $success,
                'reason' => $reason
            ]);
            if ($success) return redirect('home');
            return redirect()->route('login')->with('error', 'Invalid user or password');
        } catch (\Exception $e) {
            return redirect()->route('login')->with('error', 'Invalid user or password: ' . $e->getMessage());
        }
    }

    /**
     * Enable / Disable User
     * @param int $idUser
     * @return array{success: bool, message: string, data: null|idUser}
     */
    public function enableOrDisable(int $idUser) 
    {
        try {
            // Get user
            $user = User::find($idUser);
            if (!$user) return $this->response(false, "User not found", 404);

            // Change user active
            $user->active = !$user->active;
            $user->save();
            // Return response
            return $this->response(true, "Ok", 200, $user->active);

        } catch (\Exception $e) {
            return $this->response(false, "error to enable or disable user: " . $e->getMessage(), 500);
        }
    }
}
