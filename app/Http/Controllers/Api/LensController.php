<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lens;
use Illuminate\Http\Request;

class LensController extends Controller
{
    public function types()
    {
        return response()->json([
            'data' => collect(Lens::TYPE_LABELS)
                ->map(fn ($label, $value) => [
                    'value' => $value,
                    'label' => $label,
                ])
                ->values(),
        ]);
    }

    public function index(Request $request)
    {
        $query = Lens::query()->active();

        if ($request->filled('lens_type')) {
            $query->where('lens_type', $request->input('lens_type'));
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        $perPage = max(1, min(50, (int) $request->input('per_page', 20)));
        return response()->json([
            'data' => $query->orderBy('sort_order')->orderBy('name')->paginate($perPage),
        ]);
    }

    public function show($id)
    {
        return response()->json([
            'data' => Lens::query()->active()->findOrFail($id),
        ]);
    }
}
