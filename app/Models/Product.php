<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\Variant;

class Product extends Model
{
    use HasFactory;
    protected $table = 'products';

    protected $fillable = ['title', 'description', 'price', 'discountPrice', 'status', 'images', 'user_id'];
    // protected $guarded = [];

    protected $casts = [
        'images' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function variants()
    {
        return $this->hasMany(Variant::class);
    }

    // Add this accessor to convert stored image paths to URLs
    public function getImagesAttribute($value)
    {
        $images = json_decode($value, true);

        if (is_array($images)) {
            return array_map(fn($path) => Storage::url($path), $images);
        }

        return [];
    }
}
