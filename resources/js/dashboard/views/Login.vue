<template>
  <div class="main-content-container container-fluid h-100 px-4">
    <d-row no-gutters class="h-100">
      <d-col lg="3" md="5" class="auth-form mx-auto my-auto">
        <d-card>
          <d-card-body>
            <!-- Logo -->
            <img class="auth-form__logo d-table mx-auto mb-3" src="@/dashboard/assets/images/shards-dashboards-logo.svg" alt="Shards Dashboards - Login Template">

            <!-- Title -->
            <h5 class="auth-form__title text-center mb-4">Access Your Account</h5>

            <!-- Form Fields -->
            <d-form>
              <div class="form-group">
                <label for="exampleInputEmail1">Email address</label>
                <d-input type="email" id="exampleInputEmail1" placeholder="Enter email" />
              </div>
              <div class="form-group">
                <label for="exampleInputPassword1">Password</label>
                <d-input type="password" id="exampleInputPassword1" placeholder="Password" />
              </div>
              <div class="form-group mb-3 d-table mx-auto">
                <d-checkbox value="remember">Remember me for 30 days.</d-checkbox>
              </div>
              <d-button pill type="submit" class="btn-accent d-table mx-auto">Access Account</d-button>
            </d-form>
          </d-card-body>

          <!-- Social Icons -->
          <d-card-footer class="border-top">
            <ul class="auth-form__social-icons d-table mx-auto">
              <li><a href="#"><i class="fab fa-facebook-f"></i></a></li>
              <li><a href="#"><i class="fab fa-twitter"></i></a></li>
              <li><a href="#"><i class="fab fa-github"></i></a></li>
              <li><a href="#"><i class="fab fa-google-plus-g"></i></a></li>
            </ul>
          </d-card-footer>
        </d-card>

        <!-- Meta Details -->
        <div class="auth-form__meta d-flex mt-4">
          <d-link tag="a" to="forgot-password">Forgot your password?</d-link>
          <d-link tag="a" to="register" class="ml-auto">Create a new account?</d-link>
        </div>
      </d-col>
    </d-row>
  </div>
</template>
