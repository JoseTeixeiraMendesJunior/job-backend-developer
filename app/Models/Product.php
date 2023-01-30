<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $table = 'products';

    protected $fillable = [
        'id',
        'name',
        'price',
        'description',
        'category',
        'image_url',
    ];

    /**
     * Retorna a consulta na tabela products, buscando todos os registros e podendo aplicar filtros:
     * search => busca nas colunas name e category, os atributos que contenham o filtro search;
     * category => busca na coluna category os atributos que sejam equivalentes ao filtro category;
     * with_image => busca na coluna image_url, com a condicional true => campos com imagem, difetente de true => campos sem imagem;
     * @param array $filters
     * @return mixed
     */
    public static function getProducts(array $filters = [])
    {
        return Product::
            when(isset($filters['search']), function ($w) use ($filters) {
                $w->where('name', 'like', '%'. $filters['search'] .'%')->orWhere('category', 'like', '%'. $filters['search'] .'%');
            })
            ->when(isset($filters['category']), function ($w) use ($filters) {
                $w->where('category', $filters['category']);
            })
            ->when(isset($filters['with_image']), function ($w) use ($filters) {
                $filters['with_image'] === 'true' ? $w->whereNotNull('image_url') : $w->whereNull('image_url');
            })
            ->get();
    }
}
