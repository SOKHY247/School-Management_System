<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Str;

class TeacherController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
       $search = $request->query('search') ?? $request->query('name');
       $query = Teacher::select('id', 'name', 'gender', 'email', 'phone', 'date_of_birth', 'address', 'status', 'image');
       $query->when($search, function ($q, $search) {
           return $q->where('name', 'like', "%{$search}%");
       });

       $teachers = $query->forPage($request->page ?? 1, $request->limit ?? 2)->get();
       if(!empty($search)){
           $query->where('name', 'LIKE', "%{$search}%");
       }
        $count = Teacher::count();
        $current_user = Auth::user();
        return response()->json([
            'message' => 'Teachers retrieved successfully',
            'total' => $count,
            'current_user' => $current_user->email,
            'teachers' => $teachers->load('subjects',)
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
        // only users with @school.com email can create teachers
        if (!Str::endsWith(auth()->user()->email, '@school.com')) {
            return response()->json([
                'message' => 'Only teachers can create other teachers.'
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'gender'=> 'required|string|max:255',
            'email' => 'required|email|unique:table_teachers,email',
            'phone' => 'required|string|max:20',
            'date_of_birth' => 'required|date',
            'address' => 'required|string',
            'subject' => 'required|string|max:255',
            'status' => 'nullable|in:active,inactive',
            'image'  => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('teachers', 'public');
        }
        $teacher = Teacher::create($validated);
        return response()->json([
            'message' => 'Teacher created successfully',
            'teacher' => $teacher
        ], 201);
    }
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $teacher = Teacher::find($id);
        if (!$teacher) {
            return response()->json([
                'message' => 'Teacher not found'
            ], 404);
        }
        return response()->json([
            'message' => 'Teacher retrieved successfully',
            'teacher' => $teacher
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
        $teacher = Teacher::find($id);
        if(!$teacher){
            return response ([
                'message' => 'Teacher not found'
            ], 404  
            );
        }
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'gender'=> 'required|string|max:255',
            'email' => 'required|email|unique:table_teachers,email',
            'phone' => 'required|string|max:20',
            'date_of_birth' => 'required|date',
            'address' => 'required|string',
            'subject' => 'required|string|max:255',
            'status' => 'nullable|in:active,inactive',
            'image'  => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($request->hasFile('image')) {
            if ($teacher->image) {
                Storage::disk('public')->delete($teacher->image);
            }
            $validated['image'] = $request->file('image')->store('teachers', 'public');
        }
        $teacher->update($validated);
        return response()->json([
            'message' => 'Teacher updated successfully',
            'teacher' => $teacher
        ]);
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $teacher = Teacher::find($id);
        if (!$teacher) {
            return response()->json([
                'message' => 'Teacher not found'
                ], 404);
        }
        $teacher->delete();
        return response()->json([
            'message' => 'Teacher deleted successfully'
        ]);
    }
}
