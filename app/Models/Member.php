<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'penalty_end_at'];
    public $timestamps = false; // Menonaktifkan timestamps

    public function borrows()
    {
        return $this->hasMany(Borrow::class);
    }

    public function isPenalized()
    {
        return $this->penalty_end_at && now()->lessThan($this->penalty_end_at);
    }
}
