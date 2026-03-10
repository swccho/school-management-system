<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ResetPasswordUserRequest;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\Role;
use App\Models\Staff;
use App\Models\Student;
use App\Models\StudentGuardian;
use App\Models\User;
use App\Services\ActivityLogService;
use App\Services\DateTimeFormatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function __construct(
        private DateTimeFormatter $dateTimeFormatter,
        private ActivityLogService $activityLog
    ) {}

    public function index(Request $request): JsonResponse
    {
        if (! $request->user()->hasPermission('view-users')) {
            abort(403, 'Unauthorized.');
        }

        $query = User::query()->with('roles');

        $query->when($request->filled('search'), function ($q) use ($request) {
            $term = '%' . $request->input('search') . '%';
            $q->where(function ($sub) use ($term) {
                $sub->where('name', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('username', 'like', $term)
                    ->orWhere('phone', 'like', $term);
            });
        });
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('user_type')) {
            $query->where('user_type', $request->input('user_type'));
        }
        if ($request->filled('role_id')) {
            $query->whereHas('roles', fn ($q) => $q->where('roles.id', $request->input('role_id')));
        }
        if ($request->filled('created_from')) {
            $query->whereDate('created_at', '>=', $request->input('created_from'));
        }
        if ($request->filled('created_to')) {
            $query->whereDate('created_at', '<=', $request->input('created_to'));
        }

        $users = $query->orderBy('name')->get()->map(fn (User $u) => $this->userToArray($u));

        return response()->json($users);
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $validated = collect($request->validated())->except(['role_ids', 'avatar', 'link_type', 'link_id'])->all();
        $validated['password'] = Hash::make($validated['password']);

        if ($request->hasFile('avatar')) {
            $validated['avatar'] = $request->file('avatar')->store('users/avatars', 'public');
        }

        $user = DB::transaction(function () use ($validated, $request) {
            $user = User::create($validated);
            $user->roles()->sync($request->input('role_ids', []));

            $linkType = $request->input('link_type');
            $linkId = $request->input('link_id');
            if ($linkType && $linkId) {
                $this->setEntityUserId($linkType, $linkId, $user->id);
            }

            return $user->load('roles');
        });

        $this->activityLog->log('users', 'create', User::class, $user->id, "User created: {$user->name}", [], $request);

        return response()->json([
            'message' => 'User created.',
            'user' => $this->userToArray($user),
        ], 201);
    }

    public function show(Request $request, User $user): JsonResponse
    {
        if (! $request->user()->hasPermission('view-users')) {
            abort(403, 'Unauthorized.');
        }

        $user->load(['roles', 'staff', 'student', 'studentGuardians']);

        $data = $this->userToArray($user);
        $data['roles'] = $user->roles->map(fn (Role $r) => ['id' => $r->id, 'name' => $r->name, 'slug' => $r->slug]);
        $data['staff'] = $user->staff ? ['id' => $user->staff->id, 'full_name' => $user->staff->full_name] : null;
        $data['student'] = $user->student ? ['id' => $user->student->id, 'full_name' => $user->student->full_name] : null;
        $data['student_guardians'] = $user->studentGuardians->map(fn ($g) => ['id' => $g->id, 'name' => $g->name]);

        return response()->json($data);
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $validated = collect($request->validated())->except(['role_ids', 'avatar', 'link_type', 'link_id'])->all();
        if (! empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $validated['avatar'] = $request->file('avatar')->store('users/avatars', 'public');
        }

        DB::transaction(function () use ($validated, $request, $user) {
            $user->update($validated);
            $user->roles()->sync($request->input('role_ids', []));

            $this->clearUserLinks($user->id);

            $linkType = $request->input('link_type');
            $linkId = $request->input('link_id');
            if ($linkType && $linkId) {
                $this->setEntityUserId($linkType, $linkId, $user->id);
            }
        });

        $user->load('roles');

        $this->activityLog->log('users', 'update', User::class, $user->id, "User updated: {$user->name}", [], $request);

        return response()->json([
            'message' => 'User updated.',
            'user' => $this->userToArray($user),
        ]);
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        if (! $request->user()->hasPermission('delete-users')) {
            abort(403, 'Unauthorized.');
        }

        if ($user->id === $request->user()->id) {
            return response()->json(['message' => 'You cannot delete your own account.'], 422);
        }

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }
        $name = $user->name;
        $userId = $user->id;
        $user->roles()->detach();
        $user->delete();

        $this->activityLog->log('users', 'delete', User::class, $userId, "User deleted: {$name}", [], $request);

        return response()->json(['message' => 'User deleted.']);
    }

    public function activate(Request $request, User $user): JsonResponse
    {
        if (! $request->user()->hasPermission('edit-users')) {
            abort(403, 'Unauthorized.');
        }

        $user->update(['status' => 'active']);

        $this->activityLog->log('users', 'activate', User::class, $user->id, "User activated: {$user->name}", [], $request);

        return response()->json([
            'message' => 'User activated.',
            'user' => $this->userToArray($user->load('roles')),
        ]);
    }

    public function deactivate(Request $request, User $user): JsonResponse
    {
        if (! $request->user()->hasPermission('edit-users')) {
            abort(403, 'Unauthorized.');
        }

        if ($user->id === $request->user()->id) {
            return response()->json(['message' => 'You cannot deactivate your own account.'], 422);
        }

        $user->update(['status' => 'inactive']);

        $this->activityLog->log('users', 'deactivate', User::class, $user->id, "User deactivated: {$user->name}", [], $request);

        return response()->json([
            'message' => 'User deactivated.',
            'user' => $this->userToArray($user->load('roles')),
        ]);
    }

    public function resetPassword(ResetPasswordUserRequest $request, User $user): JsonResponse
    {
        $user->update(['password' => Hash::make($request->input('password'))]);

        $this->activityLog->log('users', 'reset-password', User::class, $user->id, "Password reset for user: {$user->name}", [], $request);

        return response()->json(['message' => 'Password reset successfully.']);
    }

    public function optionsRoles(Request $request): JsonResponse
    {
        if (! $request->user()->hasPermission('view-users')) {
            abort(403, 'Unauthorized.');
        }

        $roles = Role::orderBy('name')->get(['id', 'name', 'slug']);

        return response()->json($roles);
    }

    public function optionsLinkableEntities(Request $request): JsonResponse
    {
        if (! $request->user()->hasPermission('view-users')) {
            abort(403, 'Unauthorized.');
        }

        $userId = $request->query('user_id');
        $staffQuery = Staff::query();
        $studentQuery = Student::query();
        $guardianQuery = StudentGuardian::query();

        if ($userId) {
            $staffQuery->whereNull('user_id')->orWhere('user_id', $userId);
            $studentQuery->whereNull('user_id')->orWhere('user_id', $userId);
            $guardianQuery->whereNull('user_id')->orWhere('user_id', $userId);
        } else {
            $staffQuery->whereNull('user_id');
            $studentQuery->whereNull('user_id');
            $guardianQuery->whereNull('user_id');
        }

        $staffs = $staffQuery->get(['id', 'first_name', 'last_name'])->map(fn ($s) => [
            'id' => $s->id,
            'label' => $s->full_name,
            'type' => 'staff',
        ]);
        $students = $studentQuery->get(['id', 'first_name', 'last_name'])->map(fn ($s) => [
            'id' => $s->id,
            'label' => $s->full_name,
            'type' => 'student',
        ]);
        $guardians = $guardianQuery->get(['id', 'name'])->map(fn ($g) => [
            'id' => $g->id,
            'label' => $g->name,
            'type' => 'guardian',
        ]);

        return response()->json([
            'staffs' => $staffs,
            'students' => $students,
            'guardians' => $guardians,
        ]);
    }

    private function clearUserLinks(int $userId): void
    {
        Staff::where('user_id', $userId)->update(['user_id' => null]);
        Student::where('user_id', $userId)->update(['user_id' => null]);
        StudentGuardian::where('user_id', $userId)->update(['user_id' => null]);
    }

    private function setEntityUserId(string $linkType, int $linkId, int $userId): void
    {
        $table = match ($linkType) {
            'staff' => 'staffs',
            'student' => 'students',
            'guardian' => 'student_guardians',
            default => null,
        };
        if ($table) {
            DB::table($table)->where('id', $linkId)->update(['user_id' => $userId]);
        }
    }

    private function userToArray(User $u): array
    {
        return [
            'id' => $u->id,
            'school_id' => $u->school_id,
            'name' => $u->name,
            'email' => $u->email,
            'phone' => $u->phone,
            'username' => $u->username,
            'user_type' => $u->user_type,
            'avatar' => $u->avatar,
            'avatar_url' => $u->avatar ? Storage::disk('public')->url($u->avatar) : null,
            'status' => $u->status,
            'last_login_at' => $u->last_login_at?->toIso8601String(),
            'last_login_at_formatted' => $this->dateTimeFormatter->formatDateTime($u->last_login_at),
            'created_at' => $u->created_at->toIso8601String(),
            'created_at_formatted' => $this->dateTimeFormatter->formatDateTime($u->created_at),
            'updated_at' => $u->updated_at->toIso8601String(),
            'updated_at_formatted' => $this->dateTimeFormatter->formatDateTime($u->updated_at),
            'roles' => $u->relationLoaded('roles')
                ? $u->roles->map(fn (Role $r) => ['id' => $r->id, 'name' => $r->name, 'slug' => $r->slug])
                : [],
        ];
    }
}
