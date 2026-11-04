<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Pembelajaran extends Model
{
    protected $table = 'pembelajarans';
    protected $fillable = ['judul', 'deskripsi', 'kategori', 'thumbnail', 'link_video'];
}
