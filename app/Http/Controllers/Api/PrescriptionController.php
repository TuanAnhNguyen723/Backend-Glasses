<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PrescriptionController extends Controller
{
    public function index(Request $request)
    {
        // TODO: Implement prescription list
        return response()->json(['message' => 'Prescription list functionality not implemented yet']);
    }

    public function store(Request $request)
    {
        // TODO: Implement create prescription
        return response()->json(['message' => 'Create prescription functionality not implemented yet']);
    }

    public function update(Request $request, $id)
    {
        // TODO: Implement update prescription
        return response()->json(['message' => 'Update prescription functionality not implemented yet']);
    }

    public function destroy($id)
    {
        // TODO: Implement delete prescription
        return response()->json(['message' => 'Delete prescription functionality not implemented yet']);
    }
}
