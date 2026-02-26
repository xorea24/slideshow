<?php

namespace App\Services;

use App\Http\Resources\PositionResource;
use App\Repositories\PositionRepository;
use Illuminate\Support\Facades\DB;

class PositionService
{


    public function __construct(
        private PositionRepository $positionRepository
    ) {}

    /**
     * Get all positions
     */

    
    public function getAllPositions($request)
    {
        try {
            $per_page = $request && $request->filled('paginate') ? $request->paginate : 1000;
            $filters = [];

            if ($request) {
                $filters = [
                    'search' => $request->filled('search') ? $request->search : null
                ];
            }

            $business_industries = $this->positionRepository
                ->all($filters)
                ->paginate($per_page);

            return [
                'data' => PositionResource::collection($business_industries),
                'page' => $business_industries->currentPage(),
                'last_page' => $business_industries->lastPage(),
                'per_page' => $business_industries->perPage(),
                'total' => $business_industries->total(),
                'from' => $business_industries->firstItem(),
                'to' => $business_industries->lastItem()
            ];
        } catch (\Throwable $t) {
            throw $t;
        }
    }

    /**
     * Get positions for DataTables
     */
    public function getPositionsForDatatable($request)
    {
        try {
            return $this->positionRepository->getPositionsForDatatable($request);
        } catch (\Throwable $t) {
            throw $t;
        }
    }

    /**
     * Get position by ID
     */
    public function getPositionById($id)
    {
        try {
            return new PositionResource($this->positionRepository->find($id));
        } catch (\Throwable $t) {
            throw $t;
        }
    }

    /**
     * Create position
     */
   // App/Services/PositionService.php

    public function createPosition(array $data)
    {
        // Siguraduhin na 'name' at 'description' ang column names sa database mo
        return Position::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ]);
    }

    /**
     * Update position
     */
    public function updatePosition($id, $data)
    {
        try {
            return DB::transaction(function() use($id, $data) {
                return new PositionResource($this->positionRepository->update($id, $data));
            });
        } catch (\Throwable $t) {
            throw $t;
        }
    }

    /**
     * Delete position
     */
    public function deletePosition($id)
    {
        try {
            return DB::transaction(function() use($id) {
                return $this->positionRepository->delete($id);
            });
        } catch (\Throwable $t) {
            throw $t;
        }
    }
}