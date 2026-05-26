<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lens;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LensController extends Controller
{
    public function index()
    {
        return view('admin.lenses.index');
    }

    public function getLenses(Request $request)
    {
        $query = Lens::query();

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('lens_type', 'like', "%{$search}%");
            });
        }

        if ($request->filled('lens_type')) {
            $query->where('lens_type', $request->input('lens_type'));
        }

        if ($request->has('status') && $request->input('status') !== '') {
            $query->where('is_active', $request->input('status') === 'active');
        }

        $perPage = max(1, min(50, (int) $request->input('per_page', 10)));
        return response()->json(
            $query->orderByDesc('created_at')->orderByDesc('id')->paginate($perPage)
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sku' => 'required|string|max:100|unique:lenses,sku',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'base_price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'requires_prescription' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        $lens = Lens::create([
            ...$validated,
            'lens_type' => 'myopia',
            'slug' => $this->makeUniqueSlug($validated['name']),
            'requires_prescription' => $request->boolean('requires_prescription', true),
            'is_active' => $request->boolean('is_active', true),
            'sort_order' => (int) Lens::max('sort_order') + 1,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Lens đã được tạo thành công.',
            'data' => $lens,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $lens = Lens::findOrFail($id);

        $validated = $request->validate([
            'sku' => 'required|string|max:100|unique:lenses,sku,' . $lens->id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'base_price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'requires_prescription' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        $slug = $lens->name !== $validated['name']
            ? $this->makeUniqueSlug($validated['name'], $lens->id)
            : $lens->slug;

        $lens->update([
            ...$validated,
            'slug' => $slug,
            'requires_prescription' => $request->boolean('requires_prescription', $lens->requires_prescription),
            'is_active' => $request->boolean('is_active', $lens->is_active),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Lens đã được cập nhật.',
            'data' => $lens,
        ]);
    }

    public function destroy($id)
    {
        $lens = Lens::findOrFail($id);
        $lens->delete();

        return response()->json([
            'success' => true,
            'message' => 'Lens đã được xóa.',
        ]);
    }

    public function getFilters()
    {
        $usedTypes = Lens::query()
            ->whereNotNull('lens_type')
            ->distinct()
            ->orderBy('lens_type')
            ->pluck('lens_type')
            ->values();

        return response()->json([
            'lens_types' => collect(Lens::TYPE_LABELS)
                ->map(fn ($label, $value) => [
                    'value' => $value,
                    'label' => $label,
                    'is_used' => $usedTypes->contains($value),
                ])
                ->values(),
        ]);
    }

    private function makeUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        if ($base === '') {
            $base = 'lens';
        }

        $slug = $base;
        $counter = 1;
        while (
            Lens::query()
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $counter++;
            $slug = $base . '-' . $counter;
        }

        return $slug;
    }
}
