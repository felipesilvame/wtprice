<template>
  <d-container fluid class="main-content-container h-100 px-4">
    <d-row no-gutters class="h-100">
      <d-col lg="3" md="5" class="auth-form mx-auto my-auto">
        <d-card>
          <d-card-body>
            <!-- Logo -->
            <img class="auth-form__logo d-table mx-auto mb-3" src="@/assets/images/shards-dashboards-logo.svg" alt="Shards Dashboards - Change Password Template">

            <!-- Title -->
            <h5 class="auth-form__title text-center mb-4">Change Password</h5>

            <!-- Form Fields -->
            <d-form>
              <div class="form-group">
                <label for="exampleInputPassword1">Password</label>
                <d-input type="password" id="exampleInputPassword1" placeholder="Password" />
              </div>
              <div class="form-group mb-4">
                <label for="exampleInputPassword2">Repeat Password</label>
                <d-input type="password" id="exampleInputPassword2" placeholder="Repeat Password" />
              </div>
              <button type="submit" class="btn btn-pill btn-accent d-table mx-auto">Change Password</button>
            </d-form>
          </d-card-body>
        </d-card>
      </d-col>
    </d-row>
  </d-container>
</template>

