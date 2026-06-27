<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
	
    protected $fillable = [
        'title',
        'slug',
        'f_thumbnail',
        'short_desc',
        'description',
        'extra_desc',
        'cost_price',
        'sale_price',
        'box_price',
        'piece_price',
        'pieces_per_box',
        'old_price',
        'start_date',
        'end_date',
        'is_discount',
        'is_stock',
        'sku',
        'stock_status_id',
        'stock_qty',
        'u_stock_qty',
        'category_ids',
        'cat_id',
        'brand_id',
        'collection_id',
        'label_id',
        'variation_color',
        'shades',
        'variation_size',
        'tax_id',
        'is_featured',
        'is_publish',
        'user_id',
        'lan',
        'og_title',
        'og_image',
        'og_description',
        'og_keywords',
    ];

    protected $casts = [
        'shades' => 'array',
    ];
}
