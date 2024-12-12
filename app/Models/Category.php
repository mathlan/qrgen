<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'photo',
    ];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function recipes()
    {
        return $this->hasMany(Recipe::class);
    }
}
