<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function search(Request $request) {
        $request->validate([
            'limit' => 'integer|min:1|max:100',
            'page' => 'integer|min:1',
        ]);
        $request->mergeIfMissing(['query' => '', 'limit' => 10, 'page' => 1]);
        $query = $request->query('query');
        $limit = $request->query('limit');
        $offset = intval($limit) * ($request->integer('page') - 1);
        $result = Tag::select('*')
            ->where('name', 'LIKE', "%{$query}%");
        $count = $result->count();
        $tags = $result->offset($offset)
            ->limit($limit)
            ->get();
        return response()->json(['tags' => $tags, 'count' => $count]);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
        $this->authorize('accessAdmin', User::class);
        $request->validate([
            'name' => 'required|unique:tag,name',
        ]);
        $tag = Tag::create(['name' => $request->name]);
        session()->flash('success', 'Tag created successfully.');
        return response()->json(['tag' => $tag]);
    }

    public function delete(Request $request, int $id) {
        $tag = Tag::findOrFail($id);
        $this->authorize('accessAdmin', User::class);
        $tag->delete();
        session()->flash('success', 'Tag deleted successfully.');
        return response()->json(['message' => 'Tag deleted successfully.']);
    }

    /**
     * Display the specified resource.
     */
    public function show(Tag $tag)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tag $tag)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Tag $tag)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tag $tag)
    {
        //
    }
}
