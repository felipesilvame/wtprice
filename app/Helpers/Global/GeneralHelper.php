<?php

if (! function_exists('app_name')) {
    /**
     * Helper to grab the application name.
     *
     * @return mixed
     */
    function app_name()
    {
        return config('app.name');
    }
}

if (! function_exists('gravatar')) {
    /**
     * Access the gravatar helper.
     */
    function gravatar()
    {
        return app('gravatar');
    }
}

if (! function_exists('home_route')) {
    /**
     * Return the route to the "home" page depending on authentication/authorization status.
     *
     * @return string
     */
    function home_route()
    {
        if (auth()->check()) {
            if (auth()->user()->can('view backend')) {
                return 'admin.dashboard';
            }

            return 'frontend.user.dashboard';
        }

        return 'frontend.index';
    }
}

if (! function_exists('dot_to_array')) {
  function dot_to_array($str) {
      $output = '';
      $chucks = explode('.', $str);
      for($i = 0; $i < count($chucks); $i++){
        if (is_numeric($chucks[$i])) {
          $output .= '['.$chucks[$i].']';
        }
        else{
          $output .= '["'.$chucks[$i].'"]';
        }
      }

      return $output;
  }
}
if (! function_exists('logo_tienda')) {
  function logo_tienda($nombre){
    switch ($nombre) {
      case 'Lider':
        return 'https://www.lider.cl/images/lider-logo.svg';
        break;
      case 'Paris':
        return 'https://www.paris.cl/on/demandware.static/-/Library-Sites-ParisSharedLibrary/es_CL/dw6ac1f04c/content/paris-logo.svg';
        break;
      case 'Falabella':
        return 'https://upload.wikimedia.org/wikipedia/commons/6/6a/Falabella.svg';
        break;
      case 'ABCDin':
        return 'https://www.abcdin.cl/wcsstore/ABCDIN/logo/logo.svg';
        break;
      case 'Jumbo':
        return 'https://vignette.wikia.nocookie.net/mall/images/f/f3/Jumbo_old_logo.png/revision/latest?cb=20130623203742&path-prefix=es';
        break;
      case 'Corona':
        return 'data:image/png;base64,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';
        break;
      case 'Ripley';
        return 'https://upload.wikimedia.org/wikipedia/en/0/0c/Ripley_logo.svg';
        break;
      case 'LaPolar':
        return 'https://upload.wikimedia.org/wikipedia/commons/7/76/Logotipo_La_Polar.svg';
        break;
      default:
        return '#';
        break;
    }
  }
}
