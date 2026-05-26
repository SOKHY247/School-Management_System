<?php

namespace App\Http\Controllers;

use App\Models\ClassStudent;
use App\Models\Student;
use Auth;
use Illuminate\Http\Request;
use Str;
// use Illuminate\Support\Str;
class ClassController extends Controller
{
    public function index(Request $request)
{
    $searchTerm = $request->query('search') ?? $request->query('class_name');

    $query = ClassStudent::select('id', 'class_name', 'section', 'teacher_id');

    $query->when($searchTerm, function ($q) use ($searchTerm) {
        return $q->where('class_name', 'LIKE', '%' . $searchTerm . '%');
    });
    $count_total = $query->count();

    $classes = $query->forPage($request->page ?? 1, $request->limit ?? 2)->get();

    if ($classes->isEmpty()) {
        return response()->json([
            'message' => 'Classes not found'
        ], 404);
    }
    $current_user = Auth::user();
    return response()->json([
        'message' => 'Classes retrieved successfully',
        'total_results' => $count_total,
        'current_user' => $current_user->email,
        'classes' => $classes->load('teacher', 'students')
    ]);
}
    public function store(Request $request)
    {

     if (!Str::endsWith(auth()->user()->email, '@school.com')) {
            return response()->json([
                'message' => 'Only teachers can create class.'
            ], 403);
        }
        $validated = $request->validate([
            'class_name' => 'required|string|max:255',
            'section'    => 'required|string|max:255',
            'teacher_id' => 'required|integer|exists:table_teachers,id',
            'student_ids'=> 'nullable|array',
            'student_ids.*' => 'integer|exists:table_students,id',
        ]);
       
        $class = ClassStudent::create([
            'class_name' => $validated['class_name'],
            'section'    => $validated['section'],
            'teacher_id' => $validated['teacher_id'],
        ]);

        if (!empty($validated['student_ids'])) {
            Student::whereIn('id', $validated['student_ids'])
                ->update(['class_id' => $class->id]);
        }

        return response()->json([
            'message' => 'Class created successfully',
            'class'   => $class->load('teacher', 'students')
        ], 201);
    }
    public function show(string $id)
    {
        $class = ClassStudent::with('teacher', 'students')->find($id);
        if (!$class) {
            return response()->json([
                'message' => 'Class not found'
                ], 404);
        }
        return response()->json($class);
    }
    public function update(Request $request, string $id)
    {
        $class = ClassStudent::find($id);
        if (!$class) {
            return response()->json(['message' => 'Class not found'], 404);
        }
        $validated = $request->validate([
            'class_name'    => 'sometimes|required|string|max:255',
            'section'       => 'sometimes|required|string|max:255',
            'teacher_id'    => 'sometimes|required|integer|exists:table_teachers,id',
            'subject_id'    => 'nullable|integer|exists:table_subjects,id',
            'student_ids'   => 'nullable|array',
            'student_ids.*' => 'integer|exists:table_students,id',
        ]);
        if($class->teacher_id != Auth::id()){
            return response()->json([
                'message' => 'You are not to update this class, make sure login user is teacher that created this class.'
            ], 403);
        }
        $class->update([
            'class_name' => $validated['class_name'] ?? $class->class_name,
            'section'    => $validated['section'] ?? $class->section,
            'teacher_id' => $validated['teacher_id'] ?? $class->teacher_id,
        ]);

        if (isset($validated['student_ids'])) {
            Student::where('class_id', $class->id)->update(['class_id' => null]);
            Student::whereIn('id', $validated['student_ids'])
                ->update(['class_id' => $class->id]);
        }

        return response()->json([
            'message' => 'Class updated successfully',
            'class'   => $class->load('teacher', 'students')
        ]);
    }

    public function destroy(string $id)
    {
        $class = ClassStudent::find($id);
        if (!$class) {
            return response()->json([
                'message' => 'Class not found'
                ], 404);
        }
        Student::where('class_id', $class->id)->update(['class_id' => null]);
        $class->delete();

        return response()->json([
            'message' => 'Class deleted successfully'
            ]);
    }
}
