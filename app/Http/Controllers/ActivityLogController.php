<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Activity;
use App\Models\User;

class ActivityLogController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:auditoria_ver');
    }

    public function index(Request $request)
    {
        $users = User::orderBy('name')->pluck('name', 'id');

        $subjects = collect(Activity::select('subject_type')
            ->whereNotNull('subject_type')
            ->distinct()
            ->pluck('subject_type'))
            ->mapWithKeys(fn($class) => [$class => (new Activity)->setAttribute('subject_type', $class)->module_name])
            ->sort()
            ->toArray();

        $query = Activity::with([
            'causer' => fn($q) => $q->select('id', 'name', 'email'),
            'subject',
        ])->orderBy('created_at', 'desc');

        if ($request->filled('causer_id')) {
            $query->where('causer_id', $request->causer_id);
        }

        if ($request->filled('action_type')) {
            $query->where('description', $request->action_type);
        }

        if ($request->filled('subject_type')) {
            $query->where('subject_type', $request->subject_type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $perPage = $request->get('per_page', 15);
        if (!in_array($perPage, [15, 25, 50, 100])) {
            $perPage = 15;
        }

        $activities = $query->paginate($perPage)->appends($request->except('page'));

        return view('admin.audit.index', compact('activities', 'users', 'subjects', 'perPage'));
    }

    public function show(Activity $activityLog)
    {
        $activityLog->load([
            'causer' => fn($q) => $q->select('id', 'name', 'email'),
            'subject',
        ]);

        return view('admin.audit.show', ['log' => $activityLog]);
    }

    public function create(): never
    {
        abort(403, 'El módulo de auditoría es de solo lectura.');
    }

    public function store(): never
    {
        abort(403, 'El módulo de auditoría es de solo lectura.');
    }

    public function edit(): never
    {
        abort(403, 'El módulo de auditoría es de solo lectura.');
    }

    public function update(): never
    {
        abort(403, 'El módulo de auditoría es de solo lectura.');
    }

    public function destroy(): never
    {
        abort(403, 'El módulo de auditoría es de solo lectura.');
    }
}
