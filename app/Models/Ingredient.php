<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Ingredient extends Model
{
    use HasUuids;

    protected $table = 'ingredients';
    protected $fillable = [
        'name',
        'quantity',
        'recipe_id',
    ];

    public function recipe()
    {
        return $this->belongsTo(Recipe::class);
    }
}
