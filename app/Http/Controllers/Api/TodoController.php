<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Todo;
use Illuminate\Http\Request;

class TodoController extends Controller
{
    public function index()
    {
        return Todo::orderByDesc('id')->get();
    }
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:100',
            'completed' => 'boolean'
        ]);
        $todo = Todo::create($data);
        return response()->json($todo, 201);
    }
    public function show(Todo $todo)
    {
        return $todo;
    }
    public function update(Request $request, Todo $todo)
    {
        $data = $request->validate([
            'title' => 'sometimes|string|max:100',
            'completed' => 'sometimes|boolean'
        ]);
        $todo->update($data);
        return $todo;
    }
    public function destroy(Todo $todo)
    {
        $todo->delete();
        return response()->noContent(); // 204
    }
}
