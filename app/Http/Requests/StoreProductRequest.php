<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreProductRequest extends FormRequest
{

    /**
     * Lista de validação dos dados do request, de acordo com as regras de negócio.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' =>  'required|unique:products|max:100',
            'price' => 'required|numeric',
            'description' => 'required',
            'category' => 'required|max:200',
            'image_url' => 'url|nullable',
        ];
    }

    /**
     * Lista de exceções das regras de negócio, tratadas.
     * @return array
     */
    public function messages()
    {
        return [
          'name.required' => 'O campo name é obrigatório.',
          'name.unique' => 'O campo name deve conter um valor único, valor informado já existente.',
          'name.max' => 'O campo name tem um tamanho máximo de 100 caracteres.',
          'price.required' => 'O campo price é obrigatório.',
          'price.numeric' => 'O campo price deve ser um número do tipo float.',
          'description.required' => 'O campo description é obrigatório.',
          'category.required' => 'O campo category é obrigatório.',
          'category.max' => 'O campo category tem um tamanho máximo de 200 caracteres.',
          'image_url.url' => 'O campo image_url deve ser uma URL válida.',
        ];
    }

    /**
     * Dispara um Http Reponse Exception, contendo as falhas de validações refentes as rules
     * @param Validator $validator
     * @return void
     */
    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json($validator->errors(), 422));
    }
}
