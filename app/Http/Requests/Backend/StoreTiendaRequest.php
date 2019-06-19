<?php

namespace App\Http\Requests\Backend;

use App\Http\Request\BaseFormRequest;

/**
 *
 */
class StoreTiendaRequest extends BaseFormRequest
{

  /**
   * Determine if the user is authorized to make this request.
   *
   * @return bool
   */
  public function authorize()
  {
      return $this->user()->isAdmin();
  }

  /**
 * Get the validation rules that apply to the request.
 *
 * @return array
 */
  public function rules()
  {
      return [
        'nombre' => 'required',
        'protocolo' => 'required|in:http,https',
        'method' => 'nullable|in:GET,POST,DELETE,PUT,PATCH,HEAD,OPTIONS',
        'campo_precio_referencia' => 'required',
      ];
  }
  public function messages()
  {
      return [

      ];
  }
  /**
   *  Filters to be applied to the input.
   *
   * @return array
   */
  public function filters()
  {
      return [
          'nombre' => 'trim|escape',
          'protocolo' => 'trim|lowercase',
          'method' => 'trim|uppercase',
          'prefix_api' => 'trim',
          'suffix_api' => 'trim',
          'headers' => 'trim|cast:array',
          'querystring' => 'trim|cast:array',
          'campo_nombre_producto' => 'trim|strip_tags|escape',
          'campo_precio_referencia' => 'trim|strip_tags|escape',
          'campo_precio_oferta' => 'trim|strip_tags|escape',
          'campo_precio_tarjeta' => 'trim|strip_tags|escape',
          'url_prefix_compra' => 'trim|lowercase',
          'url_suffix_compra' => 'trim|lowercase',
          'campo_slug_compra' => 'trim|lowercase',

      ];
  }
}
