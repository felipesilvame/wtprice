<template>
  <div class="main-content-container container-fluid h-100 px-4">
    <d-row no-gutters class="h-100">
      <d-col lg="3" md="5" class="auth-form mx-auto my-auto">
        <d-card>
          <d-card-body>
            <!-- Logo -->
            <img class="auth-form__logo d-table mx-auto mb-3" src="@/assets/images/shards-dashboards-logo.svg" alt="Shards Dashboards - Forgot Password Template">

            <!-- Title -->
            <h5 class="auth-form__title text-center mb-4">Reset Password</h5>

            <!-- Form Fields -->
            <d-form>
              <div class="form-group mb-4">
                <label for="exampleInputEmail1">Email address</label>
                <d-input type="email" id="exampleInputEmail1" placeholder="Enter email" />
                <d-form-text class="text-muted text-center">You will receive an email with a unique token.</d-form-text>
              </div>
              <button type="submit" class="btn btn-pill btn-accent d-table mx-auto">Reset Password</button>
            </d-form>
          </d-card-body>
        </d-card>

        <!-- Meta -->
        <div class="auth-form__meta d-flex mt-4">
          <d-link tag="a" to="login" class="mx-auto">Take me back to login.</d-link>
        </div>
      </d-col>
    </d-row>
  </div>
</template>

