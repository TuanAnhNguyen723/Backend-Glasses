<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index($productId)
    {
        // TODO: Implement review list
        return response()->json(['message' => 'Review list functionality not implemented yet']);
    }

    public function store(Request $request, $productId)
    {
        // TODO: Implement create review
        return response()->json(['message' => 'Create review functionality not implemented yet']);
    }

    public function update(Request $request, $id)
    {
        // TODO: Implement update review
        return response()->json(['message' => 'Update review functionality not implemented yet']);
    }

    public function destroy($id)
    {
        // TODO: Implement delete review
        return response()->json(['message' => 'Delete review functionality not implemented yet']);
    }
}
