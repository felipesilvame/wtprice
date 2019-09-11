<template>
  <d-container fluid class="main-content-container px-4">
    <d-row>
      <d-col lg="8" class="mx-auto mt-4">
        <d-card class="card-small edit-user-details mb-4">
          <d-card-header class="p-0">

            <!-- User Details - Background -->
            <div class="edit-user-details__bg">
              <img src="@/dashboard/assets/images/user-profile/up-user-details-background.jpg" alt="User Details Background Image">
              <label class="edit-user-details__change-background">
                            <i class="material-icons mr-1">&#xE439;</i> Change Background Photo
                            <d-input class="d-none" type="file" />
                          </label>
            </div>

          </d-card-header>
          <d-card-body class="p-0">

            <!-- Nav Wrapper -->
            <div class="border-bottom clearfix d-flex">
              <d-nav tabs class="border-0 mt-auto mx-4 pt-2">
                <d-nav-item active>General</d-nav-item>
              </d-nav>
            </div>

            <d-form class="py-4">
              <d-form-row class="mx-4">
                <d-col class="mb-3">
                  <d-form-text tag="h6" class="m-0">General</d-form-text>
                  <d-form-text tag="p" class="text-muted m-0">Setup your general profile details.</d-form-text>
                </d-col>
              </d-form-row>
              <d-form-row class="mx-4">
                <d-col lg="8">
                  <d-form-row>

                    <!-- First Name -->
                    <d-col md="6" class="form-group">
                      <label for="firstName">First Name</label>
                      <d-input type="text" id="firstName" value="Sierra" />
                    </d-col>

                    <!-- Last Name -->
                    <d-col md="6" class="form-group">
                      <label for="lastName">Last Name</label>
                      <d-input type="text" id="lastName" value="Brooks" />
                    </d-col>

                    <!-- Location -->
                    <d-col md="6" class="form-group">
                      <label for="userLocation">Location</label>
                      <d-input-group seamless>
                        <d-input-group-text slot="prepend">
                          <i class="material-icons">&#xE0C8;</i>
                        </d-input-group-text>
                        <d-input id="userLocation" value="Remote" />
                      </d-input-group>
                    </d-col>

                    <!-- Phone Number -->
                    <d-col md="6" class="form-group">
                      <label for="phoneNumber">Phone Number</label>
                      <d-input-group seamless>
                        <d-input-group-text slot="prepend">
                          <i class="material-icons">&#xE0CD;</i>
                        </d-input-group-text>
                        <d-input id="phoneNumber" value="+40 1234 567 890" />
                      </d-input-group>
                    </d-col>

                    <!-- Email Address -->
                    <d-col md="6" class="form-group">
                      <label for="emailAddress">Email</label>
                      <d-input-group seamless>
                        <d-input-group-text slot="prepend">
                          <i class="material-icons">&#xE0BE;</i>
                        </d-input-group-text>
                        <d-input id="emailAddress" />
                      </d-input-group>
                    </d-col>

                    <!-- Display Email Publicly -->
                    <d-col md="6" class="form-group">
                      <label for="displayEmail">Display Email Publicly</label>
                      <d-select :value="null">
                        <option :value="null">Select an Option</option>
                        <option>Yes, display my email</option>
                        <option>No, do not display my email.</option>
                      </d-select>
                    </d-col>

                  </d-form-row>
                </d-col>

                <!-- User Profile Picture -->
                <d-col lg="4">
                  <label for="userProfilePicture" class="text-center w-100 mb-4">Profile Picture</label>
                  <div class="edit-user-details__avatar m-auto">
                    <img src="@/dashboard/assets/images/avatars/0.jpg" alt="User Avatar">
                    <label class="edit-user-details__avatar__change">
                                <i class="material-icons mr-1">&#xE439;</i>
                                <d-input type="file" id="userProfilePicture" class="d-none" />
                              </label>
                  </div>
                  <d-button size="sm" class="btn-white d-table mx-auto mt-4"><i class="material-icons">&#xE2C3;</i> Upload Image</d-button>
                </d-col>

              </d-form-row>

              <d-form-row class="mx-4">

                <!-- User Bio -->
                <d-col md="6" class="form-group">
                  <label for="userBio">Bio</label>
                  <d-textarea style="min-height: 87px;" id="userBio" name="userBio" />
                </d-col>

                <!-- User Tags -->
                <d-col md="6" class="form-group">
                  <label for="userTags">Tags</label>
                  <tags-input class="form-control" id="userTags" v-model="tag" :tags="userTags" />
                </d-col>

              </d-form-row>

              <hr />

              <!-- Social Profiles -->
              <d-form-row class="mx-4">
                <d-col class="mb-3 form-group">
                  <h6 class="form-text m-0">Social</h6>
                  <p class="form-text text-muted m-0">Setup your social profiles info.</p>
                </d-col>
              </d-form-row>

              <d-form-row class="mx-4">

                <!-- Facebook -->
                <d-col md="4" class="form-group">
                  <label for="socialFacebook">Facebook</label>
                  <d-input-group seamless>
                    <d-input-group-text slot="prepend">
                      <i class="fab fa-facebook-f"></i>
                    </d-input-group-text>
                    <d-input id="socialFacebook" />
                  </d-input-group>
                </d-col>

                <!-- Twitter -->
                <d-col md="4" class="form-group">
                  <label for="socialTwitter">Twitter</label>
                  <d-input-group seamless>
                    <d-input-group-text slot="prepend">
                      <i class="fab fa-twitter"></i>
                    </d-input-group-text>
                    <d-input id="socialTwitter" />
                  </d-input-group>
                </d-col>

                <!-- GitHub -->
                <d-col md="4" class="form-group">
                  <label for="socialGitHub">GitHub</label>
                  <d-input-group seamless>
                    <d-input-group-text slot="prepend">
                      <i class="fab fa-github"></i>
                    </d-input-group-text>
                    <d-input id="socialGitHub" />
                  </d-input-group>
                </d-col>

                <!-- Slack -->
                <d-col md="4" class="form-group">
                  <label for="socialSlack">Slack</label>
                  <d-input-group seamless>
                    <d-input-group-text slot="prepend">
                      <i class="fab fa-slack"></i>
                    </d-input-group-text>
                    <d-input id="socialSlack" />
                  </d-input-group>
                </d-col>

                <!-- Dribbble -->
                <d-col md="4" class="form-group">
                  <label for="socialDribbble">Dribbble</label>
                  <d-input-group seamless>
                    <d-input-group-text slot="prepend">
                      <i class="fab fa-dribbble"></i>
                    </d-input-group-text>
                    <d-input id="socialDribbble" />
                  </d-input-group>
                </d-col>

                <!-- Google Plus -->
                <d-col md="4" class="form-group">
                  <label for="socialGooglePlus">Google Plus</label>
                  <d-input-group seamless>
                    <d-input-group-text slot="prepend">
                      <i class="fab fa-google-plus-g"></i>
                    </d-input-group-text>
                    <d-input id="socialGooglePlus" />
                  </d-input-group>
                </d-col>
              </d-form-row>

              <hr />

              <!-- Notifications -->
              <d-form-row class="mx-4">
                <d-col class="mb-3">
                  <d-form-text tag="h6" class="m-0">Notifications</d-form-text>
                  <d-form-text tag="p" class="text-muted m-0">Setup which notifications would you like to receive.</d-form-text>
                </d-col>
              </d-form-row>

              <!-- Conversations -->
              <d-form-row class="mx-4">
                <d-col tag="label" for="conversationsEmailsToggle" class="col-form-label">
                  Conversations
                  <d-form-text class="text-muted">
                    Sends notification emails with updates for the conversations you are participating in or if someone mentions you.
                  </d-form-text>
                </d-col>
                <d-col class="d-flex">
                  <d-checkbox checked class="ml-auto my-auto" id="conversationsEmailsToggle" toggle/>
                </d-col>
              </d-form-row>

              <!-- New Projects -->
              <d-form-row class="mx-4">
                <d-col tag="label" for="newProjectsEmailsToggle" class="col-form-label">
                  New Projects
                  <d-form-text class="text-muted">
                    Sends notification emails when you are invited to a new project.
                  </d-form-text>
                </d-col>
                <d-col class="d-flex">
                  <d-checkbox class="ml-auto my-auto" id="newProjectsEmailsToggle" toggle/>
                </d-col>
              </d-form-row>

              <!-- Vulnerability Alerts -->
              <d-form-row class="mx-4">
                <d-col tag="label" for="vulnerabilitiesEmailsToggle" class="col-form-label">
                  Vulnerability Alerts
                  <d-form-text class="text-muted">
                    Sends notification emails when everything goes down and there's no hope left whatsoever.
                  </d-form-text>
                </d-col>
                <d-col class="d-flex">
                  <d-checkbox checked class="ml-auto my-auto" id="vulnerabilitiesEmailsToggle" toggle/>
                </d-col>
              </d-form-row>

              <hr />

              <!-- Change Password -->
              <d-form-row class="mx-4">
                <d-col class="mb-3">
                  <d-form-text tag="h6" class="m-0">Change Password</d-form-text>
                  <d-form-text tag="p" class="text-muted m-0">Change your current password.</d-form-text>
                </d-col>
              </d-form-row>

              <d-form-row class="mx-4">

                <!-- Old Password -->
                <d-col md="4" class="form-group">
                  <label for="oldPassword">Old Password</label>
                  <d-input id="oldPassword" placeholder="Old Password" />
                </d-col>

                <!-- New Password -->
                <d-col md="4" class="form-group">
                  <label for="newPassword">New Password</label>
                  <d-input id="newPassword" placeholder="New Password" />
                </d-col>

                <!-- Repeat New Password -->
                <d-col md="4" class="form-group">
                  <label for="repeatNewPassword">Repeat New Password</label>
                  <d-input id="repeatNewPassword" placeholder="Repeat New Password" />
                </d-col>

              </d-form-row>
            </d-form>
          </d-card-body>

          <!-- Save Changes -->
          <d-card-footer class="border-top">
            <d-button size="sm" class="btn-accent ml-auto d-table mr-3">
              Save Changes
            </d-button>
          </d-card-footer>

        </d-card>
      </d-col>
    </d-row>
  </d-container>
</template>

<script>
import TagsInput from '@johmun/vue-tags-input';
import '@/dashboard/assets/scss/vue-tags-input.scss';

export default {
  components: {
    TagsInput,
  },
  data() {
    return {
      tag: '',
      userTags: [{
        text: 'User Experience',
        tiClasses: ['valid'],
      },
      {
        text: 'UI Design',
        tiClasses: ['valid'],
      },
      {
        text: 'React JS',
        tiClasses: ['valid'],
      },
      {
        text: 'HTML & CSS',
        tiClasses: ['valid'],
      },
      {
        text: 'JavaScript',
        tiClasses: ['valid'],
      },
      {
        text: 'Bootstrap 4',
        tiClasses: ['valid'],
      },
      ],
    };
  },
};
</script>
