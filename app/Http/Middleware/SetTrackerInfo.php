<?php

namespace App\Http\Middleware;

use Closure;
use Browser;
use App\Models\Device;

class SetTrackerInfo
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
      $user_device = null;
      if (\Browser::isDesktop()) {
          $user_device = "Desktop";
      } else if(\Browser::isTablet()){
          $user_device = 'Tablet';
      } else if (\Browser::isMobile()) {
          $user_device = 'Mobile';
      }
      if (!session()->get('tracked', false)) {
        Device::create([
          'user_agent' => mb_strimwidth(\Browser::userAgent(), 0, 190, '...'),
          'session_id' => session()->getId(),
          'user_device' => $user_device,
          'browser' => \Browser::browserName(),
          'operating_system' => \Browser::platformName(),
        ]);

        session()->put('tracked', true);
      }
      return $next($request);
    }
}
