<?php

namespace App\Repositories;

use App\Repositories\BaseRepository;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Class TiendaRepository
 */
class TiendaRepository extends BaseRepository
{
  /**
   * @return string
   */
  public function model()
  {
      return \App\Models\Tienda::class;
  }

  /**
   * @param int    $paged
   * @param string $orderBy
   * @param string $sort
   *
   * @return mixed
   */
  public function allPaginated($paged = 25, $orderBy = 'created_at', $sort = 'desc') : LengthAwarePaginator
  {
      return $this->model
          ->orderBy($orderBy, $sort)
          ->paginate($paged);
  }
}
