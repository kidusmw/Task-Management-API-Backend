<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['title', 'description', 'price', 'discountPrice', 'status', 'images'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
