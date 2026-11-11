<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Pembelajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PembelajaranController extends Controller
{
    public function index(Request $request)
    {
        $pembelajaran = Pembelajaran::query()
            ->when($request->kategori, function ($query, $kategori) {
                $query->where('kategori', $kategori);
            })
            ->when($request->judul, function ($query, $judul) {
                $query->where('judul', 'like', "%$judul%");
            })
            ->get();

        return view('user.pembelajaran.index', compact('pembelajaran'));
    }

    public function details($id)
    {
        $pembelajaran = Pembelajaran::findOrFail($id);
        return view('user.pembelajaran.details', compact('pembelajaran'));
    }

}
