<?php


namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function __construct()
    {
        // $this->middleware('auth');
        // Nanti bisa ditambah permission middleware
        // $this->middleware('permission:categories.read')->only(['index', 'show']);
        // $this->middleware('permission:categories.create')->only(['create', 'store']);
        // $this->middleware('permission:categories.update')->only(['edit', 'update']);
        // $this->middleware('permission:categories.delete')->only(['destroy']);
    }

    // Tampilkan daftar categories dalam tree structure
    public function index(Request $request)
    {
        $query = Category::with(['parent', 'children'])
                        ->withCount('items');

        // Filter by parent (untuk show subcategories)
        if ($request->filled('parent')) {
            $query->where('parent_id', $request->parent);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('category_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Get tree structure atau flat list
        if ($request->filled('view') && $request->view === 'flat') {
            $categories = $query->orderBy('category_name')->paginate(15);
            $viewType = 'flat';
        } else {
            // Tree view - ambil root categories dengan children
            $categories = $query->root()->orderBy('category_name')->paginate(15);
            $viewType = 'tree';
        }

        return view('categories.index', compact('categories', 'viewType'));
    }

    // Tampilkan form create category
    public function create()
    {
        $parentOptions = Category::getTreeOptions();
        return view('categories.create', compact('parentOptions'));
    }

    // Store category baru
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_name' => 'required|string|max:100|unique:categories,category_name',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|string|exists:categories,category_id',
            'is_active' => 'boolean',
        ], [
            'category_name.required' => 'Nama kategori wajib diisi.',
            'category_name.unique' => 'Nama kategori sudah digunakan.',
            'parent_id.exists' => 'Kategori induk tidak valid.',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Validasi circular reference (category tidak boleh jadi parent dari dirinya sendiri)
            if ($request->filled('parent_id')) {
                $parent = Category::find($request->parent_id);
                if ($parent && $this->wouldCreateCircularReference($parent, null)) {
                    return back()
                        ->withInput()
                        ->with('error', 'Tidak dapat membuat circular reference pada kategori.');
                }
            }

            // Generate category ID
            $categoryId = $this->generateCategoryId();

            $category = Category::create([
                'category_id' => $categoryId,
                'category_name' => $request->category_name,
                'description' => $request->description,
                'parent_id' => $request->parent_id ?: null,
                'is_active' => $request->boolean('is_active', true),
            ]);

            // Log activity
            $this->logActivity('categories', $category->category_id, 'create', null, $category->toArray());

            return redirect()->route('categories.index')
                ->with('success', 'Kategori berhasil ditambahkan!');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal menambahkan kategori: ' . $e->getMessage());
        }
    }

    // Tampilkan detail category
    public function show(Category $category)
    {
        $category->load(['parent', 'children.children', 'items']);
        $breadcrumb = $category->getBreadcrumb();

        return view('categories.show', compact('category', 'breadcrumb'));
    }

    // Tampilkan form edit category
    public function edit(Category $category)
    {
        $parentOptions = Category::getTreeOptions($category->parent_id, $category->category_id);
        return view('categories.edit', compact('category', 'parentOptions'));
    }

    // Update category
    public function update(Request $request, Category $category)
    {
        $validator = Validator::make($request->all(), [
            'category_name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('categories', 'category_name')->ignore($category->category_id, 'category_id')
            ],
            'description' => 'nullable|string',
            'parent_id' => 'nullable|string|exists:categories,category_id',
            'is_active' => 'boolean',
        ], [
            'category_name.required' => 'Nama kategori wajib diisi.',
            'category_name.unique' => 'Nama kategori sudah digunakan.',
            'parent_id.exists' => 'Kategori induk tidak valid.',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Validasi circular reference
            if ($request->filled('parent_id') && $request->parent_id !== $category->parent_id) {
                $parent = Category::find($request->parent_id);
                if ($parent && $this->wouldCreateCircularReference($parent, $category->category_id)) {
                    return back()
                        ->withInput()
                        ->with('error', 'Tidak dapat membuat circular reference pada kategori.');
                }
            }

            $oldData = $category->toArray();

            $category->update([
                'category_name' => $request->category_name,
                'description' => $request->description,
                'parent_id' => $request->parent_id ?: null,
                'is_active' => $request->boolean('is_active', true),
            ]);

            // Log activity
            $this->logActivity('categories', $category->category_id, 'update', $oldData, $category->fresh()->toArray());

            return redirect()->route('categories.index')
                ->with('success', 'Kategori berhasil diupdate!');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal mengupdate kategori: ' . $e->getMessage());
        }
    }

    // Delete category
    public function destroy(Category $category)
    {
        try {
            // Cek apakah category memiliki children
            if ($category->hasChildren()) {
                return back()->with('error', 'Kategori tidak dapat dihapus karena memiliki sub-kategori!');
            }

            // Cek apakah category memiliki items
            if ($category->items()->exists()) {
                return back()->with('error', 'Kategori tidak dapat dihapus karena memiliki barang!');
            }

            $oldData = $category->toArray();
            $categoryId = $category->category_id;
            $category->delete();

            // Log activity
            $this->logActivity('categories', $categoryId, 'delete', $oldData, null);

            return back()->with('success', 'Kategori berhasil dihapus!');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus kategori: ' . $e->getMessage());
        }
    }

    // Toggle category status
    public function toggleStatus(Category $category)
    {
        try {
            $oldData = $category->toArray();
            $category->update(['is_active' => !$category->is_active]);

            $status = $category->is_active ? 'diaktifkan' : 'dinonaktifkan';
            $action = $category->is_active ? 'activate' : 'deactivate';

            $this->logActivity('categories', $category->category_id, $action, $oldData, $category->fresh()->toArray());

            return back()->with('success', "Kategori berhasil {$status}!");

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengubah status kategori: ' . $e->getMessage());
        }
    }

    // API endpoint untuk get children categories
    public function getChildren(Request $request, $parentId = null)
    {
        $categories = Category::active()
            ->where('parent_id', $parentId)
            ->orderBy('category_name')
            ->get(['category_id', 'category_name']);

        return response()->json($categories);
    }

    // Helper method untuk generate category ID
    private function generateCategoryId(): string
    {
        $lastCategory = Category::orderBy('category_id', 'desc')->first();
        $lastNumber = $lastCategory ? (int) substr($lastCategory->category_id, 3) : 0;
        $newNumber = $lastNumber + 1;
        return 'CAT' . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }

    // Helper method untuk check circular reference
    private function wouldCreateCircularReference($parent, $categoryId): bool
    {
        $current = $parent;

        while ($current) {
            if ($current->category_id === $categoryId) {
                return true;
            }
            $current = $current->parent;
        }

        return false;
    }

    // Helper method untuk log activity
    private function logActivity($tableName, $recordId, $action, $oldData, $newData)
    {
        try {
            $lastLog = \App\Models\ActivityLog::orderBy('log_id', 'desc')->first();
            $lastNumber = $lastLog ? (int) substr($lastLog->log_id, 3) : 0;
            $newNumber = $lastNumber + 1;
            $logId = 'LOG' . str_pad($newNumber, 8, '0', STR_PAD_LEFT);

            \App\Models\ActivityLog::create([
                'log_id' => $logId,
                'user_id' => Auth::id(),
                'table_name' => $tableName,
                'record_id' => $recordId,
                'action' => $action,
                'old_values' => $oldData,
                'new_values' => $newData,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log activity: ' . $e->getMessage());
        }
    }
}
