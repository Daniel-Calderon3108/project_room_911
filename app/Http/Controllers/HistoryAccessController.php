<?php

namespace App\Http\Controllers;

use App\Models\HistoryAccess;
use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class HistoryAccessController extends Controller
{

    use ApiResponse;

    /**
     * Get history access by employee id.
     * @param int $id
     * @return array{success: bool, message: string, data: null|HistoryAccess[]} 
     */
    public function show(int $id)
    {
        try {
            // Get the history access by employee id
            $historyAccess = HistoryAccess::where('employee_id', $id)->get();
            // Return the history access
            return $this->response(true, "OK", 200, $historyAccess);
        } catch (\Exception $e) {
            return $this->response(false, 'Error getting the history access: ' . $e->getMessage(), 500);
        }
    }
}
