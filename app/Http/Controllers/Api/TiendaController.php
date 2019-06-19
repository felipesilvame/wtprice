<?php

namespace App\Http\Controllers\Api;

use App\Models\Tienda;
use App\Http\Controllers\Controller;
use App\Repositories\TiendaRepository;
use App\Http\Requests\Backend\ManageTiendaRequest;
use App\Http\Requests\Backend\StoreTiendaRequest;

/**
 *
 */
class TiendaController extends Controller
{
  /**
   * @var TiendaRepository
   */
  protected $tiendaRepository;

  /**
   * TiendaRepository constructor.
   *
   * @param TiendaRepository $tiendaRepository
   */
  public function __construct(TiendaRepository $tiendaRepository)
  {
      $this->tiendaRepository = $tiendaRepository;
  }

  public function index(ManageTiendaRequest $request){
    return response()->json($this->tiendaRepository->allPaginated(25, 'id', 'asc'));
  }
}
