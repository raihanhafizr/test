<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;

    protected $table = 'books';
    protected $fillable = ['code', 'title', 'author', 'stock'];
    public $timestamps = false;

    public function borrows()
    {
        return $this->hasMany(Borrow::class);
    }

    public function availableStock()
    {
        $borrowed = $this->borrows()->whereNull('returned_at')->count();
        return $this->stock - $borrowed;
    }
}
