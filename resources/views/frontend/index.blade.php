@extends('frontend.layouts.app')

@section('title', app_name() . ' | ' . __('navs.general.home'))

@section('content')
    <div class="row">
      @forelse($items as $item)
        @if($item->producto)
        <div class="col-6 col-md-4 col-lg-3 mb-2">
            <div class="card h-100">
                <div class="card-body">
                  <img src="{{ logo_tienda($item->producto->tienda->nombre )}}" alt="logo" style="max-width: 100%;max-height:50px">
                    <p>Tienda: {{ $item->producto->tienda->nombre }}</p>
                    <p>Nombre: {{ $item->producto->nombre }}</p>
                    <p>Precio normal: {{ moneyFormat($item->precio_referencia, 'CLP')}}</p>
                    @if($item->precio_oferta)
                      <p>Precio Oferta:  <strong>{{ moneyFormat($item->precio_oferta, 'CLP')}}</strong></p>
                    @endif
                    @if($item->precio_tarjeta)
                      <p>Precio Tarjeta:  <strong>{{ moneyFormat($item->precio_tarjeta, 'CLP')}}</strong></p>
                    @endif
                    <p>Ultima Actualizacion: {{ $item->updated_at->diffForHumans() }} ({{ $item->updated_at }})</p>
                    <a href="{{ $item->producto->url_compra }}" target="_blank" class="btn btn-success">Ver</a>
                </div><!--card-body-->
            </div><!--card-->
        </div><!--col-->
        @endif
        @empty
         <h4>No results</h4>
        @endforelse
    </div><!--row-->
    <nav aria-label="Page navigation example" class="table-responsive mb-2">

        {{ $items->appends(request()->except('page'))->links() }}
    </nav>
@endsection
