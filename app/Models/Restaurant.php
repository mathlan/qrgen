<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Restaurant extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'photo',
        'slug',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function recipes()
    {
        return $this->hasMany(Recipe::class);
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($restaurant) {
            $baseSlug = Str::slug($restaurant->name);

            // Compter combien de restaurants existent déjà avec ce nom
            $count = static::where('slug', 'LIKE', $baseSlug . '%')
                ->where('id', '!=', $restaurant->id)
                ->count();

            // Si c'est le premier, utiliser juste le slug de base
            if ($count === 0) {
                $restaurant->slug = $baseSlug;
            } else {
                // Sinon, ajouter le numéro suivant
                $restaurant->slug = $baseSlug . ($count + 1);
            }
        });

        static::updating(function ($restaurant) {
            // Si le nom a changé, mettre à jour le slug
            if ($restaurant->isDirty('name')) {
                $baseSlug = Str::slug($restaurant->name);

                // Compter combien de restaurants existent déjà avec ce nom
                $count = static::where('slug', 'LIKE', $baseSlug . '%')
                    ->where('id', '!=', $restaurant->id)
                    ->count();

                if ($count === 0) {
                    $restaurant->slug = $baseSlug;
                } else {
                    $restaurant->slug = $baseSlug . ($count + 1);
                }
            }
        });
    }
}
