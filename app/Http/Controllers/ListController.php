<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ListController extends Controller
{
    public function index(): JsonResponse
    {
        $lists = auth()->user()->lists;

        return response()->json([
            'success' => true,
            'lists' => $lists,
        ], Response::HTTP_OK);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $list = auth()->user()->lists()->create($request->only('name'));

        return response()->json([
            'success' => true,
            'list' => $list,
        ], Response::HTTP_CREATED);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $list = auth()->user()->lists()->findOrFail($id);
        $list->update($request->only('name'));

        return response()->json([
            'success' => true,
            'list' => $list,
        ], Response::HTTP_OK);
    }

    public function destroy(int $id): JsonResponse
    {
        $list = auth()->user()->lists()->findOrFail($id);
        $list->delete();

        return response()->json([
            'success' => true,
            'message' => 'List deleted successfully',
        ], Response::HTTP_OK);
    }
}
