<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    private $product;

    public function __construct(Product $product)
    {
        $this->product = $product;
    }

    /**
     * Lista os produtos todos os produtos, com a possibilidade de aplicação de filtros.
     * @param Request $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request)
    {
        $products = $this->product->getProducts(['search' => $request->search, 'category' => $request->category, 'with_image' => $request->with_image]);

        return ProductResource::collection($products);
    }

    /**
     * Salva um novo produto
     * @param StoreProductRequest $request
     * @return ProductResource|\Illuminate\Http\JsonResponse
     */
    public function store(StoreProductRequest $request)
    {
        try{
            $new_product = $this->product::create($request->validated());

            return ProductResource::make($new_product);

        }catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao cadastrar o produto.',
            ], 404);
        }
    }

    /**
     * Retorna um produto a partir do id.
     *
     * @param  Product $product
     * @return ProductResource
     */
    public function show(Product $product)
    {
        return ProductResource::make($product);
    }

    /**
     * Atualiza os atributos de um produto.
     * @param UpdateProductRequest $request
     * @param Product $product
     * @return ProductResource|\Illuminate\Http\JsonResponse
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        try {
            $product->update($request->validated());

            return ProductResource::make($product);

        }catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar o produto.',
            ], 404);
        }
    }

    /**
     * Remove um produto
     * @param Product $product
     * @return bool|\Illuminate\Http\JsonResponse|null
     */
    public function destroy(Product $product)
    {
        try {

           return $product->delete();

        }catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar o produto.',
            ], 404);
        }
    }
}
