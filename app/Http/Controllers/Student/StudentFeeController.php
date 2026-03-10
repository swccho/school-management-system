<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\StudentFee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentFeeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->load(['student']);
        $student = $user->student;
        if (! $student) {
            return response()->json([
                'summary' => ['total_amount' => 0, 'paid_amount' => 0, 'due_amount' => 0, 'status' => null],
                'fees' => [],
                'payment_history' => [],
            ]);
        }

        $fees = StudentFee::query()
            ->where('student_id', $student->id)
            ->with('payments')
            ->orderByDesc('due_date')
            ->orderByDesc('created_at')
            ->get();

        $totalAmount = $fees->sum('total_amount');
        $paidAmount = $fees->sum('paid_amount');
        $dueAmount = $totalAmount - $paidAmount;

        $overallStatus = null;
        if ($fees->isNotEmpty()) {
            if ($dueAmount <= 0) {
                $overallStatus = 'paid';
            } elseif ($paidAmount > 0) {
                $overallStatus = 'partially_paid';
            } else {
                $hasOverdue = $fees->contains(fn ($f) => $f->due_date && $f->due_date->isPast() && $f->status !== 'paid');
                $overallStatus = $hasOverdue ? 'overdue' : 'unpaid';
            }
        }

        $feeItems = $fees->map(fn ($f) => [
            'id' => $f->id,
            'fee_category' => $f->fee_category,
            'period_label' => $f->period_label,
            'total_amount' => (float) $f->total_amount,
            'paid_amount' => (float) $f->paid_amount,
            'due_amount' => (float) ($f->total_amount - $f->paid_amount),
            'due_date' => $f->due_date?->format('Y-m-d'),
            'status' => $this->deriveStatus($f),
            'reference' => $f->reference,
        ])->toArray();

        $paymentHistory = $fees->flatMap(function ($f) {
            return $f->payments->map(fn ($p) => [
                'student_fee_id' => $f->id,
                'fee_category' => $f->fee_category,
                'period_label' => $f->period_label,
                'amount' => (float) $p->amount,
                'paid_at' => $p->paid_at?->toIso8601String(),
                'reference' => $p->reference,
            ]);
        })->sortByDesc('paid_at')->values()->take(50)->toArray();

        return response()->json([
            'summary' => [
                'total_amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'due_amount' => $dueAmount,
                'status' => $overallStatus,
            ],
            'fees' => $feeItems,
            'payment_history' => $paymentHistory,
        ]);
    }

    private function deriveStatus(StudentFee $fee): string
    {
        $due = $fee->total_amount - $fee->paid_amount;
        if ($due <= 0) {
            return 'paid';
        }
        if ($fee->paid_amount > 0) {
            return 'partially_paid';
        }
        if ($fee->due_date && $fee->due_date->isPast()) {
            return 'overdue';
        }
        return 'unpaid';
    }
}
