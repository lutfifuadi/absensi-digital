<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SchoolController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $schools = \App\Models\School::all();
        return view('admin.schools.index', compact('schools'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.schools.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'subdomain' => 'required|string|max:100|unique:schools',
            'email' => 'nullable|email',
        ]);

        \App\Models\School::create($request->all());

        return redirect()->route('admin.schools.index')->with('success', 'School created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $school = \App\Models\School::findOrFail($id);
        return view('admin.schools.edit', compact('school'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $school = \App\Models\School::findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'subdomain' => 'required|string|max:100|unique:schools,subdomain,' . $school->id,
            'email' => 'nullable|email',
        ]);

        $school->update($request->all());

        return redirect()->route('admin.schools.index')->with('success', 'School updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $school = \App\Models\School::findOrFail($id);
        $school->delete();

        return redirect()->route('admin.schools.index')->with('success', 'School deleted successfully.');
    }

    public function toggleStatus(string $id)
    {
        $school = \App\Models\School::findOrFail($id);
        $school->status = $school->status === 'active' ? 'inactive' : 'active';
        $school->save();

        return back()->with('success', 'School status updated successfully.');
    }
}
