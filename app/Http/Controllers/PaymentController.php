<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Auth;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
       $search = $request->input('search') ?? $request->input('student_id');
       $query = Payment::select('id', 'student_id', 'subject_id', 'amount', 'payment_date', 'description', 'status');
       $query->when($search, function ($q) use ($search) {
           return $q->where('student_id', $search);
       });
       $payments = $query->forPage($request->page ?? 1, $request->limit ?? 2)->get();
       $current_user = Auth::user();
       return response()->json([
           'message' => 'Payment retrieved successfully',
           'current_user' => $current_user->name,
           'data' => $payments->load('student', 'subject')
       ]);
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id'   => 'required|integer|exists:table_students,id',
            'subject_id'   => 'required|integer|exists:table_subjects,id',
            'amount'       => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'description'  => 'nullable|string',
            'status'       => 'nullable|in:paid,pending,unpaid',
        ]);
        $payment_exist = Payment::where('student_id', $validated['student_id'])->where('subject_id', $validated['subject_id'])->first();
        if($payment_exist){
            return response([
                'message' => 'Student already paid for this subject'
            ], 401);
        }
        $payment = Payment::create($validated);
        

        return response()->json([
            'message' => 'Payment created successfully',
            'payment' => $payment->load('student', 'subject')
        ], 201);
    }

    public function show(string $id)
    {
        $payment = Payment::with('student', 'subject')->find($id);
        if (!$payment) {
            return response()->json([
                'message' => 'Payment not found'
                ], 404);
        }
        return response()->json($payment);
    }
    public function update(Request $request, string $id)
    {
        $payment = Payment::find($id);

        if (!$payment) {
            return response()->json(['message' => 'Payment not found'], 404);
        }
        $validated = $request->validate([
            'student_id'   => 'sometimes|required|integer|exists:table_students,id',
            'subject_id'   => 'sometimes|required|integer|exists:table_subjects,id',
            'amount'       => 'sometimes|required|numeric|min:0',
            'payment_date' => 'sometimes|required|date',
            'description'  => 'nullable|string',
            'status'       => 'nullable|in:paid,pending,unpaid',
        ]);

        $payment->update($validated);

        return response()->json([
            'message' => 'Payment updated successfully',
            'payment' => $payment->load('student', 'subject')
        ]);
    }

    public function destroy(string $id)
    {
        $payment = Payment::find($id);

        if (!$payment) {
            return response()->json([
                'message' => 'Payment not found'],
                 404);
        }
        $payment->delete();
        return response()->json([
            'message' => 'Payment deleted successfully'
            ]);
    }
}
