<?php

$tienda = \App\Models\Tienda::create(
  [
    'nombre' => 'Lider',
    'protocolo' => 'https',
    'prefix_api' => 'api.lider.cl/black-cyber/products/?sku=',
    'suffix_api' => '&appId=BuySmart',
    'campo_nombre_producto' => '0.displayName',
    'campo_precio_referencia' => '0.price.BasePriceReference',
    'campo_precio_oferta' => '0.price.BasePriceSales',
    'campo_precio_tarjeta' => '0.price.BasePriceTLMC'
  ]
);

$tienda = \App\Models\Tienda::create(
  [
    'nombre' => 'ABC Din',
    'protocolo' => 'https',
    'prefix_api' => 'app.abcdin.cl/api/products/byPartNumber/?partNumber=',
    'suffix_api' => '&storeId=99999',
    'headers' => [
      'Authorization' => 'Bearer 40fddf613cb8a1d88ac334931afda5ec7ccf5fe3',
    ],
    'campo_nombre_producto' => 'data.products.0.name',
    'campo_precio_referencia' => 'data.products.0.price.commerce',
    'campo_precio_tarjeta' => 'data.products.0.price.card'
  ]
);

$tienda = \App\Models\Tienda::create(
  [
    'nombre' => 'Ripley',
    'protocolo' => 'https',
    'prefix_api' => 'simple.ripley.cl/api/v2/products/',
    'suffix_api' => null,
    'campo_nombre_producto' => 'name',
    'campo_precio_referencia' => 'prices.listPrice',
    'campo_precio_oferta' => 'prices.offerPrice',
    'campo_precio_tarjeta' => 'prices.cardPrice'
  ]
);

$tienda = \App\Models\Tienda::create(
  [
    'nombre' => 'Jumbo',
    'protocolo' => 'https',
    'prefix_api' => 'api.smdigital.cl:8443/v0/cl/jumbo/vtex/front/dev/proxy/api/v1/catalog_system/pub/products/search/',
    'suffix_api' => '/p?sc=11',
    'headers' => [
      'x-api-key' => 'IuimuMneIKJd3tapno2Ag1c1WcAES97j',
    ],
    'campo_nombre_producto' => '0.productName',
    'campo_precio_referencia' => '0.items.0.sellers.0.commertialOffer.ListPrice',
    'campo_precio_oferta' => '0.items.0.sellers.0.commertialOffer.Price',
    'campo_precio_tarjeta' => null
  ]
);

$tienda = \App\Models\Tienda::create(
  [
    'nombre' => 'Corona',
    'protocolo' => 'https',
    'prefix_api' => 'www.corona.cl/api/catalog_system/pub/products/search/?fq=productId:',
    'suffix_api' => null,
    'campo_nombre_producto' => '0.productName',
    'campo_precio_referencia' => '0.items.0.sellers.0.commertialOffer.ListPrice',
    'campo_precio_oferta' => '0.items.0.sellers.0.commertialOffer.Price',
    'campo_precio_tarjeta' => null
  ]
);
