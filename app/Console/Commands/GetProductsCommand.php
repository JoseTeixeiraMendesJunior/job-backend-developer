<?php

namespace App\Console\Commands;

use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class GetProductsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:import {--id=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'importação de dados de produtos de um serviço externo';


    private $importCount = 0;
    private $failCount = 0;

    /**
     * @return int
     */
    public function handle(): int
    {
        try  {
            $this->info('Iniciando importação de produtos');

            $id = $this->option('id');
            $url = "https://fakestoreapi.com/products/$id";

            $this->info('Estabelecendo conexão com a API');
            $responseProducts = Http::get("$url");

            if ($responseProducts->failed()) {
                throw new \Exception("Erro ao improtar os produtos na API: $url", -1);
            }

            $products = $responseProducts->json();

            if(!$products) {
                throw new \Exception('produto não encontrado');
            }

            if($id){
                $this->info('iniciando registro do produto');
                $this->persistProduct($products);
                $this->info('importação realizado com sucesso');
            } else {
                $this->persistProducts($products);
            }

            return 0;

        } catch (\Exception $e) {
            $this->error($e->getMessage());
            return 1;
        }

    }

    /**
     * Recebe um produto, valida os atributos e tenta persisti-lo na base de dados
     * @param $product
     * @return void
     * @throws \Exception
     */
    private function persistProduct($product)
    {
        $dataProduct = [
            'id' => $product['id'],
            'name' => $product['title'],
            'price' => $product['price'],
            'description' => $product['description'],
            'category' => $product['category'],
            'image_url' => $product['image'],
        ];

        $validation = Validator::make($dataProduct, (new UpdateProductRequest())->rules());
        if($validation->fails()) {
            throw new \Exception();
        }

        Product::updateOrCreate(['id' => $dataProduct['id']], $dataProduct);
    }

    /**
     * Recebe uma lista de produtos e gerencia a importação de cada um deles
     * @param $products
     * @return void
     */
    private function persistProducts($products)
    {
        $this->info('iniciando registro de produtos');
        $failures = '';
        $total = sizeof($products);

        foreach ($products as $product) {
            try {
                $stage = $this->importCount + $this->failCount + 1;
                $this->info("importando produto $stage/$total");

                $this->persistProduct($product);

                $this->importCount++;

            } catch (\Exception $e) {
                $this->failCount++;
                $failures .= "{$product['id']}, ";
            }
        }

        $this->info("importação finalizada");

         if($this->failCount > 0){
             $this->info("$this->importCount de $total produtos foram importados com sucesso");
             $this->error("houve falhas na importação de $this->failCount produtos, segue a lista de ids de produtos com falha de importação:");
             $this->error("$failures");
         } else {
             $this->info("todos os produtos foram importados");
         }
    }
}
