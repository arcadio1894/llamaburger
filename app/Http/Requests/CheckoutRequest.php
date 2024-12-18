<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            /*'cart_id' => 'required',*/
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'required|string|max:15',
            'email' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'paymentMethod' => 'required|integer',
        ];
    }

    public function messages()
    {
        return [
            /*'cart_id.required' => 'El id del carrito es obligatorio.',*/
            'first_name.required' => 'El nombre es obligatorio.',
            'first_name.string' => 'El nombre debe ser una cadena de texto.',
            'first_name.max' => 'El nombre no debe superar los 255 caracteres.',
            'last_name.required' => 'El apellido es obligatorio.',
            'last_name.string' => 'El apellido debe ser una cadena de texto.',
            'last_name.max' => 'El apellido no debe superar los 255 caracteres.',
            'phone.required' => 'El número de teléfono es obligatorio.',
            'phone.string' => 'El número de teléfono debe ser una cadena de texto.',
            'phone.max' => 'El número de teléfono no debe superar los 15 caracteres.',
            'email.required' => 'El correo es obligatorio.',
            'email.string' => 'El correo debe ser una cadena de texto.',
            'email.max' => 'El correo no debe superar los 255 caracteres.',
            'address.required' => 'La dirección es obligatoria.',
            'address.string' => 'La dirección debe ser una cadena de texto.',
            'address.max' => 'La dirección no debe superar los 255 caracteres.',
            'paymentMethod.required' => 'Debe seleccionar un método de pago.',
        ];
    }
}
