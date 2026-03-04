<?php

namespace App\Http\Controllers;

use App\Http\Traits\ApiResponses;
use App\Http\Resources\Select2Resource;
use App\Models\Interview;
use App\Services\InterviewService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class InterviewController extends Controller
{
    use ApiResponses;

    public function __construct(
        private InterviewService $interviewService
    ) {
        $this->middleware('auth');
        $this->middleware('is_admin');
    }

    /**
     * Display the interview management page
     */
    public function indexPage()
    {
        $page_title = 'Interviews';

        return view('admin.settings.list', [
            'page_title' => $page_title
        ]);
    }

    /**
     * Get interviews for DataTables
     */
    public function datatable(Request $request)
    {
        try {
            $data = $this->interviewService->getInterviewsForDatatable($request);
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
            $interviews = $this->interviewService->getAllInterviews($request);
            return $this->success($interviews, 'Interviews retrieved successfully');
        } catch (\Throwable $t) {
            return $this->error([$t->getMessage()], 'Failed to retrieve interviews');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $interview = $this->interviewService->getInterviewById($id);
            if ($interview) {
                return $this->success($interview, 'Interview retrieved successfully');
            } else {
                return $this->error([], 'Interview not found', 404);
            }
        } catch (\Throwable $t) {
            return $this->error([$t->getMessage()], 'Failed to retrieve interview');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'interview_date' => 'required|date|unique:interviews,interview_date',
                'position_id' => 'required|integer|exists:positions,id',
            ]);

            if ($validator->fails()) {
                return $this->error($validator->errors(), 'Validation failed', 422);
            }

            $interview = $this->interviewService->createInterview($validator->validated());
            return $this->success($interview, 'Interview created successfully', 201);
        } catch (\Throwable $t) {
            return $this->error([$t->getMessage()], 'Failed to create interview');
        }
    }

     /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'interview_date' => "required|date|unique:interviews,interview_date,$id",
                'position_id' => 'required|integer|exists:positions,id',
            ]);

            if ($validator->fails()) {
                return $this->error($validator->errors(), 'Validation failed', 422);
            }

            $interview = $this->interviewService->updateInterview($id, $validator->validated());
            return $this->success($interview, 'Interview updated successfully');
        } catch (\Throwable $t) {
            return $this->error([$t->getMessage()], 'Failed to update interview');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete($id)
    {
        try {
            $this->interviewService->deleteInterview($id);
            return $this->success([], 'Interview deleted successfully');
        } catch (\Throwable $t) {
            return $this->error([$t->getMessage()], 'Failed to delete interview');
        }
    }

    /**
     * Get interviews for Select2
     */
    public function search(Request $request)
    {
        $search = $request->input('search', '');
        $limit = $request->input('limit', 20);

        $interviews = Interview::select('id', 'name')
            ->when($search, function($query) use($search) {
                $query->where('interview_date', 'like', "%{$search}%")
                    ->orWhereHas('position', function($q) use($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            })
            ->orderBy('name')
            ->limit($limit)
            ->get();

        $results = Select2Resource::collection($interviews);

        return response()->json(['results' => $results]);
    }
}