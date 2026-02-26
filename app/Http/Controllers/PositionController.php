<?php

namespace App\Http\Controllers;

use App\Http\Traits\ApiResponses;
use App\Http\Resources\Select2Resource;
use App\Models\Position;
use App\Models\Album;
use App\Services\PositionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PositionController extends Controller
{
    use ApiResponses;

    public function __construct(
        private PositionService $positionService
    ) {
        $this->middleware('auth');
        $this->middleware('is_admin');
    }

    /**
     * Display the position management page
     */
    public function indexPage()
    {
        // Reuse the albums dashboard-style page here as well
        $albums = Album::with('photos')->orderBy('name')->get();

        return view('admin.albums.list', [
            'title'  => 'Albums Management',
            'albums' => $albums,
        ]);
    }

    /**
     * Get positions for DataTables
     */
    public function datatable(Request $request)
    {
        try {
            $data = $this->positionService->getPositionsForDatatable($request);
            return response()->json($data);
        } catch (\Exception $e) {
            return $this->error([$e->getMessage()]);
        }
    }

    /**
     * Display a listing of the resource (API).
     */
    public function index(Request $request)
    {
        try {
            $positions = $this->positionService->getAllPositions($request);
            return $this->success($positions, 'Positions retrieved successfully');
        } catch (\Throwable $t) {
            return $this->error([$t->getMessage()], 'Failed to retrieve positions');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $position = $this->positionService->getPositionById($id);
            if ($position) {
                return $this->success($position, 'Position retrieved successfully');
            } else {
                return $this->error([], 'Position not found', 404);
            }
        } catch (\Throwable $t) {
            return $this->error([$t->getMessage()], 'Failed to retrieve position');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255|unique:positions,name,NULL,id,deleted_at,NULL',
                'description' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return $this->error($validator->errors(), 'Validation failed', 422);
            }

            $position = $this->positionService->createPosition($validator->validated());
            return $this->success($position, 'Position created successfully', 201);
        } catch (\Throwable $t) {
            return $this->error([$t->getMessage()], 'Failed to create position');
        }
    }

     /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => "required|string|max:255|unique:positions,name,$id,id,deleted_at,NULL",
                'description' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return $this->error($validator->errors(), 'Validation failed', 422);
            }

            $position = $this->positionService->updatePosition($id, $validator->validated());
            return $this->success($position, 'Position updated successfully');
        } catch (\Throwable $t) {
            return $this->error([$t->getMessage()], 'Failed to update position');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete($id)
    {
        try {
            $this->positionService->deletePosition($id);
            return $this->success([], 'Position deleted successfully');
        } catch (\Throwable $t) {
            return $this->error([$t->getMessage()], 'Failed to delete position');
        }
    }

    /**
     * Get positions for Select2
     */
    public function search(Request $request)
    {
        $search = $request->input('search', '');
        $limit = $request->input('limit', 20);

        $positions = Position::select('id', 'name')
            ->when($search, function($query) use($search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->limit($limit)
            ->get();

        $results = Select2Resource::collection($positions);

        return response()->json(['results' => $results]);
    }
}