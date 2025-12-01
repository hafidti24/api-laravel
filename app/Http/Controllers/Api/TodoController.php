<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Todo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TodoController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        return $user->todos()->orderByDesc('id')->get();
    }
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:100',
            'completed' => 'boolean',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048'
        ]);

        // upload jika ada file
        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')
                ->store('todo_attachments', 'public');
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $todo = $user->todos()->create([
            'title'           => $data['title'],
            'completed'       => $data['completed'] ?? false,
            'attachment_path' => $attachmentPath
        ]);

        return response()->json($todo, 201);
    }
    public function show(Todo $todo)
    {
        $this->authorizeOwner($todo);
        
        return $todo;
    }
    public function update(Request $request, Todo $todo)
    {
        $this->authorizeOwner($todo);

        $data = $request->validate([
            'title'      => 'sometimes|string|max:100',
            'completed'  => 'sometimes|boolean',
            'attachment' => 'sometimes|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        if (isset($data['title'])) {
            $todo->title = $data['title'];
        }
        if (isset($data['completed'])) {
            $todo->completed = $data['completed'];
        }

        if ($request->hasFile('attachment')) {
            if ($todo->attachment_path) {
                Storage::disk('public')->delete($todo->attachment_path);
            }

            $todo->attachment_path = $request->file('attachment')
                ->store('todo_attachments', 'public');
        }

        $todo->save();

        return [
            'title'      => $todo->title,
            'completed'  => $todo->completed,
            'attachment_path' => $todo->attachment_path,
        ];
    }
    public function destroy(Todo $todo)
    {
        $this->authorizeOwner($todo);

        if ($todo->attachment_path) {
            Storage::disk('public')->delete($todo->attachment_path);
        }

        $todo->delete();

        return response()->noContent();
    }

    public function getAttachment(Todo $todo)
    {
        $user = request()->user();

        if ($user->role !== 'admin' && $todo->user_id !== $user->id) {
            abort(403, 'Forbidden');
        }

        if (! $todo->attachment_path || ! Storage::disk('public')->exists($todo->attachment_path)) {
            abort(404, 'Attachment not found');
        }

        return response()->download(
            Storage::disk('public')->path($todo->attachment_path)
        );
    }

    protected function authorizeOwner(Todo $todo)
    {
        if ($todo->user_id !== Auth::id()) {
            abort(403, 'Forbidden');
        }
    }
}
