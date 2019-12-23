@extends('frontend.layouts.app')

@section('title', app_name() . ' | ' . __('navs.general.home'))

@section('content')
    <div class="row">
      @forelse($items as $item)
        @if($item->producto)
        <div class="col-12 col-md-6 col-lg-4 mb-2">
            <div class="card h-100">
                <div class="card-body row">
                    @if($item->producto->imagen_url)
                    <div class="col-4 col-lg-12 text-center">
                      <img src="{{ $item->producto->imagen_url }}" alt="img" style="max-width: 100%;max-height:150px">
                    </div>
                    @endif
                    <div class="col-{{$item->producto->imagen_url ? '8' : '12'}}">
                      <p class="m-0"><small>{{ $item->producto->tienda->nombre }}</small></p>
                      <p>{{ $item->producto->nombre }}</p>
                      @if($item->precio_tarjeta)
                        <p class="m-0">Precio Tarjeta:  <strong>{{ moneyFormat($item->precio_tarjeta, 'CLP')}}</strong></p>
                      @endif
                      @if($item->precio_oferta)
                        <p class="m-0">Precio Oferta:  <strong>{{ moneyFormat($item->precio_oferta, 'CLP')}}</strong></p>
                      @endif
                      <p class="m-0">
                        @if($item->precio_oferta || $item->precio_tarjeta)
                          <small><em><s>Precio normal: {{ moneyFormat($item->precio_referencia, 'CLP')}}</s></em></small>
                        @else
                          Precio normal: {{ moneyFormat($item->precio_referencia, 'CLP')}}
                        @endif
                      </p>
                      <div class="actions d-flex">
                        <a href="{{ $item->producto->url_compra }}" target="_blank" class="btn btn-success btn-sm">Ver</a>
                        <abbr title="{{ $item->updated_at }}" class="ml-auto"><small><em>{{ $item->updated_at->diffForHumans() }}</em></small></abbr>
                      </div>
                    </div>
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
