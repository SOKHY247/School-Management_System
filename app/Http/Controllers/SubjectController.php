<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Subject;
// use App\Models\Teacher;
use Auth;
use Illuminate\Http\Request;
use Str;
// use Illuminate\Support\Str;

class SubjectController extends Controller
{
    public function index(Request $request)
    {
       
      $shearch = $request->query('search') ?? $request->query('name');
      $query = Subject::select('id', 'name', 'code', 'description', 'status');
      $query->when($shearch, function ($q) use ($shearch) {
        return $q->where('name', 'LIKE', '%' . $shearch . '%');
      });
        
         $subjects = $query->forPage($request->page ?? 1, $request->limit ?? 2)->get();
        if($subjects->isEmpty()){
            return response()->json([
                'message' => 'Subjects not found'
            ], 404);
        }
         $count_total = Subject::count();
         $current_user = Auth::user();
        return response()->json([
            'message' => 'Subjects retrieved successfully',
            'All subjects'=> $count_total,
            'current_user'=> $current_user->email,
            'subjects' => $subjects
            
        ]);
    }

    public function store(Request $request)
    {
          if (!Str::endsWith(auth()->user()->email, '@school.com')) {
            return response()->json([
                'message' => 'Only teachers can create subjcet.'
            ], 403);
        }
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'code'        => 'required|string|max:50|unique:table_subjects,code',
            'description' => 'nullable|string',
            'status'      => 'nullable|in:active,inactive',
        ]);
    
        $subject = Subject::create($validated);
        

        return response()->json([
            'message' => 'Subject created successfully',
            'subject' => $subject->load('teachers')
        ], 201);
    }

    public function show(string $id)
    {
        $subject = Subject::find($id);
        if (!$subject) {
            return response()->json(['message' => 'Subject not found'], 404);
        }
        return response()->json($subject);
    }
    public function update(Request $request, string $id)
    {
        $subject = Subject::find($id);
        if (!$subject) {
            return response()->json(['message' => 'Subject not found'], 404);
        }
        $validated = $request->validate([
            'name'        => 'sometimes|required|string|max:255',
            'code'        => 'sometimes|required|string|max:50|unique:table_subjects,code,' . $id,
            'description' => 'nullable|string',
            'status'      => 'nullable|in:active,inactive',
        ]);

        $subject->update($validated);

        return response()->json([
            'message' => 'Subject updated successfully',
            'subject' => $subject
        ]);
    }

    public function destroy(string $id)
    {
        $subject = Subject::find($id);

        if (!$subject) {
            return response()->json(['message' => 'Subject not found'], 404);
        }
        $subject->delete();
        return response()->json(['message' => 'Subject deleted successfully']);
    }
}
