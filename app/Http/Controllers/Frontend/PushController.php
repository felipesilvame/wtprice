<?php

namespace App\Http\Controllers\Frontend;

use App\Notifications\PushRata;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Device;
use Auth;
use Notification;

class PushController extends Controller
{
  /**
   * Store the PushSubscription.
   *
   * @param \Illuminate\Http\Request $request
   * @return \Illuminate\Http\JsonResponse
   */
  public function store(Request $request){
      $this->validate($request,[
          'hashid' => 'required',
          'sub.endpoint'    => 'required',
          'sub.keys.auth'   => 'required',
          'sub.keys.p256dh' => 'required'
      ]);

      $endpoint = $request->sub['endpoint'];
      $token = $request->sub['keys']['auth'];
      $key = $request->sub['keys']['p256dh'];
      $hashid = $request->hashid;
      $user = Device::firstOrCreate([
          'fingerprint' => $hashid
      ]);

      $user->updatePushSubscription($endpoint, $key, $token);

      return response()->json(['success' => true],200);
  }

  /**
   * Send Push Notifications to all users.
   *
   * @return \Illuminate\Http\Response
   */
  public function push(){
      $product = \App\Models\Producto::orderBy('updated_at', 'DESC')->first();
      Notification::send(Device::all(),new PushRata($product, 199900, 19990));
      return response('OK');
  }
}
