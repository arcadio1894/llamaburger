<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    protected $fillable = [
        'code',
        'full_name',
        'description',
        'stock_current',
        'unit_price',
        'image',
        'category_id',
        'enable_status',
        'ingredients',
        'slug',
        'visibility_price_real',
        'date_reactivate'
    ];

    protected $appends = ['has_options', 'price_default'];

    public function options()
    {
        return $this->hasMany(Option::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class)->withTrashed();
    }

    public function productTypes()
    {
        return $this->hasMany(ProductType::class);
    }

    public function getPriceDefaultAttribute()
    {
        $product = Product::findOrFail($this->id);

        // Obtener los tipos relacionados al producto
        $productTypes = $product->productTypes()->with('type')->get();

        // Obtener el tipo por defecto
        $defaultProductType = $productTypes->where('default', true)->first();

        if ( isset($defaultProductType) )
        {
            return $defaultProductType->price;
        } else {
            return $this->unit_price;
        }

    }

    public function getHasOptionsAttribute()
    {
        // rápido y barato (si no cargaste options, cuenta en DB; si sí, usa la colección)
        if ($this->relationLoaded('options')) {
            return $this->options->count() > 0;
        }
        return $this->options()->count() > 0;
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            if (empty($product->slug)) {
                $originalSlug = Str::slug($product->full_name, '-');
                $slug = $originalSlug;
                $count = 1;

                while (Product::where('slug', $slug)->exists()) {
                    $slug = $originalSlug . '-' . $count;
                    $count++;
                }

                $product->slug = $slug;
            }
        });

        static::updating(function ($product) {
            if (empty($product->slug)) {
                $originalSlug = Str::slug($product->full_name, '-');
                $slug = $originalSlug;
                $count = 1;

                while (Product::where('slug', $slug)->exists()) {
                    $slug = $originalSlug . '-' . $count;
                    $count++;
                }

                $product->slug = $slug;
            }
        });
    }
}
