<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('name') ?? $request->query('search');
        $query = Student::select('id', 'name', 'gender', 'email', 'phone', 'date_of_birth', 'address', 'class_id', 'status', 'image');

        if (!empty($search)) {
            $query->where('name', 'LIKE', "%{$search}%");
        }

        $page       = $request->query('page', 1);
        $limit_page = $request->query('limit_page', 5);
        $students   = $query->forPage($page, $limit_page)->get();

        if ($students->isEmpty()) {
            return response()->json(['message' => 'Students not found'], 404);
        }
        $student_current_user = Auth::user();
        return response()->json([
            'message' => 'Students retrieved successfully',
            'student_current_user' => $student_current_user->name,
            'data'    => $students->load('studentClass', 'subjects', 'scores', 'payments')
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'gender'        => 'required|in:male,female',
            'email'         => 'required|email|unique:table_students,email',
            'phone'         => 'required|string|max:20',
            'date_of_birth' => 'required|date',
            'address'       => 'required|string',
            'class_id'      => 'nullable|integer|exists:table_classes,id',
            'status'        => 'nullable|in:active,inactive',
            'image'         => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('students', 'public');
        }

        $student = Student::create($validated);

        return response()->json([
            'message' => 'Student created successfully',
            'student' => $student
        ], 201);
    }

    public function show(string $id)
    {
        $student = Student::find($id);

        if (!$student) {
            return response()->json(['message' => 'Student not found'], 404);
        }

        return response()->json([
            'message' => 'Student retrieved successfully',
            'student' => $student->load('studentClass', 'subjects', 'scores', 'payments')
        ]);
    }

    public function update(Request $request, string $id)
{
    $student = Student::find($id);

    if (!$student) {
        return response()->json(['message' => 'Student not found'], 404);
    }
    // if student not this account can't update 
    if ($student->id !== Auth::id()) {
        return response()->json(['message' => 'Can not update this student, make sure login user is student that created this student.'], 403);
    }

    $validated = $request->validate([
        'name'          => 'sometimes|required|string|max:255',
        'gender'        => 'sometimes|required|in:male,female',
        'email'         => 'sometimes|required|email|unique:table_students,email,' . $id . ',id',
        'phone'         => 'sometimes|required|string|max:20',
        'date_of_birth' => 'sometimes|required|date',
        'address'       => 'sometimes|required|string',
        'class_id'      => 'nullable|integer|exists:table_classes,id',
        'status'        => 'nullable|in:active,inactive',
        'image'         => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
    ]);

    if ($request->hasFile('image')) {
        if ($student->image) {
            Storage::disk('public')->delete($student->image);
        }
        $validated['image'] = $request->file('image')->store('students', 'public');
    }

    if ($request->image_remove) {
        Storage::disk('public')->delete($student->image);
        $validated['image'] = null;
    }

    $student->update($validated);
    $student->refresh(); // ← Fix: reload fresh data from DB

    return response()->json([
        'message' => 'Student updated successfully',
        'student' => $student
    ]);
}

    public function destroy(string $id)
    {
        $student = Student::find($id);

        if (!$student) {
            return response()->json(['message' => 'Student not found'], 404);
        }

        if ($student->image) {
            Storage::disk('public')->delete($student->image);
        }
        $student->delete();
        return response()->json(['message' => 'Student deleted successfully']);
    }
}
