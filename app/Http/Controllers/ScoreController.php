<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Score;
use App\Models\Student;
use Auth;
use Illuminate\Http\Request;
use Str;

class ScoreController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
      $search = $request->input('search') ?? $request->input('student_id');
      $query = Score::select('student_id', 'subject_id', 'score');
      $query->when($search, function ($q) use ($search) {
            return $q->where('student_id', $search);
      });

      $score = $query->forPage($request->page ?? 1, $request->limit ?? 2)->get();

      $count_total = Score::count();
      $current_user = Auth::user();

      return response([
        'mes' => 'Score retrieved successfully',
        'current' => $current_user->email,
        'score' => $score,
        'all score' => $count_total,
      ]);

        
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
     if (!Str::endsWith(auth()->user()->email, '@school.com')) {
            return response()->json([
                'message' => 'Only teachers can create score.'
            ], 403);
        }
        $validated = $request->validate([
            'student_id' => 'required|integer|exists:table_students,id',
            'subject_id' => 'required|integer|exists:table_subjects,id',
            'score'      => 'required|integer|min:0|max:100',
        ]);
         
        $score = Score::create($validated);

        $total = Score::where('student_id', $validated['student_id'])->sum('score');
        Student::where('id', $validated['student_id'])->update(['score' => $total]);

        return response()->json([
            'message' => 'Score created successfully',
            'score'   => $score->load('student', 'subject')
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request,  string $id)
    {
       $score = Score::find($id);
       if(!$score){
        return response([
            'message' => 'Score not found'
        ], 404);
       }
       return response([
            'message' => 'Score retrieved successfully',
            'score' => $score
        ]);

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $score = Score::find($id);
        if (!$score) {
            return response()->json(['message' => 'Score not found'], 404);
        }
        $validated = $request->validate([
            'student_id' => 'sometimes|required|integer|exists:table_students,id',
            'subject_id' => 'sometimes|required|integer|exists:table_subjects,id',
            'score'      => 'sometimes|required|integer|min:0|max:100',
        ]);

        $score->update($validated);

        $total = Score::where('student_id', $score->student_id)->sum('score');
        Student::where('id', $score->student_id)->update(['score' => $total]);

        return response()->json([
            'message' => 'Score updated successfully',
            'score'   => $score->load('student', 'subject')
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $score = Score::find($id);
        if(!$score){
            return response([
                'message' => 'Score not found'
            ], 404);
        }
        $score->delete();
        return response([
            'message' => 'Score deleted successfully'
        ]);
    }
}
