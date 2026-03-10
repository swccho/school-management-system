<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\SystemBackup;
use App\Services\DateTimeFormatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SystemBackupController extends Controller
{
    public function __construct(
        private DateTimeFormatter $dateTimeFormatter
    ) {}

    public function index(Request $request): JsonResponse
    {
        if (! $request->user()->hasPermission('view-backups')) {
            abort(403, 'Unauthorized.');
        }

        $query = SystemBackup::query()->with('creator:id,name');

        $query->when($request->filled('search'), function ($q) use ($request) {
            $term = '%' . $request->input('search') . '%';
            $q->where('file_name', 'like', $term);
        });
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('backup_type')) {
            $query->where('backup_type', $request->input('backup_type'));
        }
        if ($request->filled('created_from')) {
            $query->whereDate('created_at', '>=', $request->input('created_from'));
        }
        if ($request->filled('created_to')) {
            $query->whereDate('created_at', '<=', $request->input('created_to'));
        }

        $backups = $query->orderByDesc('created_at')->get()
            ->map(fn (SystemBackup $b) => $this->backupToArray($b));

        return response()->json($backups);
    }

    public function show(Request $request, SystemBackup $system_backup): JsonResponse
    {
        if (! $request->user()->hasPermission('view-backups')) {
            abort(403, 'Unauthorized.');
        }

        $system_backup->load('creator:id,name,email');

        $data = $this->backupToArray($system_backup);
        $data['created_by_name'] = $system_backup->creator?->name;

        return response()->json($data);
    }

    public function generate(Request $request): JsonResponse
    {
        if (! $request->user()->hasPermission('generate-backups')) {
            abort(403, 'Unauthorized.');
        }

        $school = School::first();
        $fileName = 'backup_' . now()->format('Y-m-d_His') . '.zip';
        $filePath = 'backups/' . $fileName;

        $backup = SystemBackup::create([
            'school_id' => $school?->id,
            'file_name' => $fileName,
            'file_path' => $filePath,
            'file_size' => null,
            'backup_type' => 'manual',
            'status' => 'pending',
            'created_by' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Backup generation started. Metadata recorded.',
            'backup' => $this->backupToArray($backup->load('creator:id,name')),
        ], 201);
    }

    private function backupToArray(SystemBackup $b): array
    {
        $url = null;
        if ($b->status === 'completed' && Storage::disk('public')->exists($b->file_path)) {
            $url = Storage::disk('public')->url($b->file_path);
        }

        return [
            'id' => $b->id,
            'file_name' => $b->file_name,
            'file_path' => $b->file_path,
            'file_size' => $b->file_size,
            'file_size_formatted' => $b->file_size ? $this->formatFileSize($b->file_size) : null,
            'file_url' => $url,
            'backup_type' => $b->backup_type,
            'status' => $b->status,
            'created_by' => $b->created_by,
            'created_at' => $b->created_at->toIso8601String(),
            'created_at_formatted' => $this->dateTimeFormatter->formatDateTime($b->created_at),
        ];
    }

    private function formatFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
