<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pembelajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PembelajaranController extends Controller
{
    public function index()
    {
        $pembelajaran = Pembelajaran::latest()->get();

        return view('admin.pembelajaran.index', compact('pembelajaran'));
    }

    public function create()
    {
        return view('admin.pembelajaran.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul' => ['required', 'string', 'max:255'],
            'kategori' => ['required', 'in:Soft Skill,Hard Skill'],
            'deskripsi' => ['required', 'string'],
            'link_video' => ['required', 'url', 'max:2048'],
            'thumbnail' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('thumbnail')) {
            $validated['thumbnail'] = $request->file('thumbnail')->store('pembelajaran', 'public');
        }

        Pembelajaran::create($validated);
        return redirect()->route('pembelajaran.index')->with('success', 'Pembelajaran berhasil ditambahkan.');
    }

    public function edit()
    {
        
    }

    public function update()
    {
       
    }

    public function destroy()
    {
        
    }
}
