<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Step extends Model
{
    protected $table = 'steps';
    protected $fillable = [
        'step_number',
        'instruction',
        'recipe_id',
    ];

    public function recipe()
    {
        return $this->belongsTo(Recipe::class);
    }
}
