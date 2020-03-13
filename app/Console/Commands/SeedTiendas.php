<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SeedTiendas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seed:tiendas';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create the tiendas from the tiendas.php file';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
      \App\Models\Tienda::updateOrcreate(['nombre' => 'Lider'],
        [
          'protocolo' => 'https',
          'prefix_api' => 'buysmart-landing-bff-production.lider.cl/buysmart-checkout-bff/products/?sku=',
          'suffix_api' => '&appId=BuySmart',
          'campo_nombre_producto' => '0.displayName',
          'campo_precio_referencia' => '0.price.BasePriceReference',
          'campo_precio_oferta' => '0.price.BasePriceSales',
          'campo_precio_tarjeta' => '0.price.BasePriceTLMC',
          'campo_slug_compra' => '0.sku',
          'url_prefix_compra' => 'https://www.lider.cl/product/sku/',
          'url_suffix_compra' => null,
          'campo_imagen_url' => '0.imagesAvailables.0',
        ]
      );

      \App\Models\Tienda::updateOrcreate(['nombre' => 'ABCDin'],
        [
          'protocolo' => 'https',
          'prefix_api' => 'app.abcdin.cl/api/products/byPartNumber/?partNumber=',
          'suffix_api' => '&storeId=99999',
          'headers' => [
            'Authorization' => 'Bearer 40fddf613cb8a1d88ac334931afda5ec7ccf5fe3',
          ],
          'campo_nombre_producto' => 'data.products.0.name',
          'campo_precio_referencia' => 'data.products.0.price.commerce',
          'campo_precio_tarjeta' => 'data.products.0.price.card',
          'url_prefix_compra' => 'https://www.abcdin.cl/tienda/search/',
          'campo_imagen_url' => 'data.products.0.thumbnail',
        ]
      );

      \App\Models\Tienda::updateOrcreate(['nombre' => 'Ripley'],
        [
          'protocolo' => 'https',
          'prefix_api' => 'simple.ripley.cl/api/v2/products/',
          'suffix_api' => null,
          'campo_nombre_producto' => 'name',
          'campo_precio_referencia' => 'prices.listPrice',
          'campo_precio_oferta' => 'prices.offerPrice',
          'campo_precio_tarjeta' => 'prices.cardPrice',
          'campo_slug_compra' => 'productString',
          'url_prefix_compra' => 'https://simple.ripley.cl/',
          'url_suffix_compra' => null,
          'campo_imagen_url' => 'fullImage',
        ]
      );

      \App\Models\Tienda::updateOrcreate(['nombre' => 'Jumbo'],
        [
          'protocolo' => 'https',
          'prefix_api' => 'api.smdigital.cl:8443/v0/cl/jumbo/vtex/front/dev/proxy/api/v1/catalog_system/pub/products/search/',
          'suffix_api' => '/p?sc=11',
          'headers' => [
            'x-api-key' => 'IuimuMneIKJd3tapno2Ag1c1WcAES97j',
          ],
          'campo_nombre_producto' => '0.productName',
          'campo_precio_referencia' => '0.items.0.sellers.0.commertialOffer.ListPrice',
          'campo_precio_oferta' => '0.items.0.sellers.0.commertialOffer.Price',
          'campo_precio_tarjeta' => null,
          'campo_slug_compra' => '0.linkText',
          'url_prefix_compra' => 'https://www.jumbo.cl/',
          'url_suffix_compra' => '/p',
          'campo_imagen_url' => '0.items.0.images.0.imageUrl',
        ]
      );

      \App\Models\Tienda::updateOrcreate(['nombre' => 'Corona'],
        [
          'protocolo' => 'https',
          'prefix_api' => 'www.corona.cl/api/catalog_system/pub/products/search/?fq=productId:',
          'suffix_api' => null,
          'campo_nombre_producto' => '0.productName',
          'campo_precio_referencia' => '0.items.0.sellers.0.commertialOffer.ListPrice',
          'campo_precio_oferta' => '0.items.0.sellers.0.commertialOffer.Price',
          'campo_precio_tarjeta' => null,
          'campo_slug_compra' => '0.linkText',
          'url_prefix_compra' => 'https://www.corona.cl/',
          'url_suffix_compra' => '/p',
          'campo_imagen_url' => '0.items.0.images.0.imageUrl',
        ]
      );

      \App\Models\Tienda::updateOrcreate(['nombre' => 'Linio'],
        [
          'protocolo' => 'https',
          'prefix_api' => 'api.linio.com/mapi/p/',
          'suffix_api' => null,
          'campo_nombre_producto' => 'name',
          'campo_precio_referencia' => 'simples.0.originalPrice',
          'campo_precio_oferta' => 'simples.0.price',
          'campo_precio_tarjeta' => 'simples.0.promotionalPrices.0.amount',
          'campo_slug_compra' => 'slug',
          'url_prefix_compra' => 'https://www.linio.cl/p/',
          'method' => 'POST',
          'headers' => [
            'Accept' => 'application/json',
            'X-Version' => '2',
            'X-Auth-Store' => 'cl'
          ],
        ]
      );

      \App\Models\Tienda::updateOrcreate(['nombre' => 'Falabella'],
        [
          'protocolo' => 'https',
          'prefix_api' => 'www.falabella.com/rest/model/falabella/rest/browse/BrowseActor/product-details-get-state',
          'suffix_api' => null,
          'request_body_sku' => 'productId',
          'campo_nombre_producto' => 'state.product.displayName',
          'campo_precio_referencia' => 'state.product.prices:label,,originalPrice',
          'campo_precio_oferta' => 'state.product.prices:label,(Oferta),originalPrice',
          'campo_precio_tarjeta' => 'state.product.prices:type,1,originalPrice',
          'campo_slug_compra' => null,
          'url_prefix_compra' => 'https://www.falabella.com/falabella-cl/product/',
          'url_suffix_compra' => '/',
          'method' => 'POST',
          'headers' => [
            'Accept' => 'application/json',
            'Accept-encoding' => 'gzip',
            'User-Agent' => 'okhttp/3.10.0',
            'X-cmRef' => 'FalabellaMobileApp',
            'Content-Type' => 'application/json',
          ],
          'campo_imagen_url' => 'https://falabella.scene7.com/is/image/Falabella/'
        ]
      );

      \App\Models\Tienda::updateOrcreate(['nombre' => 'Paris'],
        [
          'protocolo' => 'https',
          'prefix_api' => 'www.paris.cl/',
          'suffix_api' => '.html',
          'request_body_sku' => 'html > body #pdpMain',
          'campo_nombre_producto' => 'html > body .js-product-name',
          'campo_precio_oferta' => 'html > body div#primary div.price-internet span[itemprop=price], html > body div#primary div.offer-price.default-price',
          'campo_precio_referencia' => 'html > body .price-normal > span[itemprop=price], html > body div#product-content div.offer-price.default-price',
          'campo_precio_tarjeta' => 'html > body .price-tc.cencosud-price, html > body .price-tc.cencosud-price-2',
          'url_prefix_compra' => 'https://www.paris.cl/',
          'url_suffix_compra' => '.html',
          'campo_slug_compra' => 'html > body div#product-content span.visually-hidden[itemprop=url]',
          'campo_imagen_url' => 'html > body #thumbnails img'
        ]
      );

      \App\Models\Tienda::updateOrcreate(['nombre' => 'LaPolar'],
        [
          'protocolo' => 'https',
          'prefix_api' => 'www.lapolar.cl/',
          'suffix_api' => '.html',
          'request_body_sku' => 'html > body .product-detail',
          'campo_nombre_producto' => 'html > body .product-name',
          'campo_precio_oferta' => 'html > body .price.js-internet-price:not([itemprop=priceSpecification]) span.price-value',
          'campo_precio_referencia' => 'html > body .js-normal-price span.price-value, html > body p[itemprop=priceSpecification]',
          'campo_precio_tarjeta' => 'html > body .price.js-tlp-price span.price-value',
          'url_prefix_compra' => 'https://www.lapolar.cl/',
          'url_suffix_compra' => '.html',
          'campo_slug_compra' => null,
          'campo_imagen_url' => 'html > body .product-detail .primary-image img'
        ]
      );
    }
}
