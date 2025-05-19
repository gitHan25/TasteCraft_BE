<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Bookmark extends Model
{
    use HasFactory;
    protected $table = 'bookmarks';
    protected $fillable = ['user_id', 'recipe_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function recipe()
    {
        return $this->belongsTo(Recipe::class);
    }
}
