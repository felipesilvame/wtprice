export default function () {
  return [{
    title: 'Dashboards',
    items: [{
      title: 'Analytics',
      to: '/analytics',
      htmlBefore: '<i class="material-icons">&#xE917;</i>',
      htmlAfter: '',
    }, {
      title: 'Online Store',
      to: '/ecommerce',
      htmlBefore: '<i class="material-icons">&#xE8D1;</i>',
      htmlAfter: '',
    }, {
      title: 'Personal Blog',
      to: '/blog-overview',
      htmlBefore: '<i class="material-icons">edit</i>',
      htmlAfter: '',
    }],
  },
  {
    title: 'Templates',
    items: [{
      title: 'User Account',
      htmlBefore: '<i class="material-icons">&#xE7FD;</i>',
      items: [{
        title: 'User Profile',
        to: {
          name: 'user-profile',
        },
      }, {
        title: 'User Profile Lite',
        to: {
          name: 'user-profile-lite',
        },
      }, {
        title: 'Edit User Profile',
        to: {
          name: 'edit-user-profile',
        },
      }, {
        title: 'Login',
        to: {
          name: 'login',
        },
      }, {
        title: 'Register',
        to: {
          name: 'register',
        },
      }, {
        title: 'Change Password',
        to: {
          name: 'change-password',
        },
      }, {
        title: 'Forgot Password',
        to: {
          name: 'forgot-password',
        },
      },
      ],
    }, {
      title: 'File Managers',
      htmlBefore: '<i class="material-icons">&#xE2C7;</i>',
      items: [{
        title: 'Files - List View',
        to: {
          name: 'file-manager-list',
        },
      }, {
        title: 'Files - Cards View',
        to: {
          name: 'file-manager-cards',
        },
      }],
    }, {
      title: 'Transaction History',
      htmlBefore: '<i class="material-icons">&#xE889;</i>',
      to: {
        name: 'transaction-history',
      },
    }, {
      title: 'Add New Post',
      htmlBefore: '<i class="material-icons">note_add</i>',
      to: {
        name: 'add-new-post',
      },
    }, {
      title: 'Errors',
      htmlBefore: '<i class="material-icons">error</i>',
      to: {
        name: 'errors',
      },
    }],
  }, {
    title: 'Components',
    items: [{
      title: 'Overview',
      htmlBefore: '<i class="material-icons">view_module</i>',
      to: {
        name: 'components-overview',
      },
    }, {
      title: 'Tables',
      htmlBefore: '<i class="material-icons">table_chart</i>',
      to: {
        name: 'tables',
      },
    }, {
      title: 'Blog Posts',
      htmlBefore: '<i class="material-icons">vertical_split</i>',
      to: {
        name: 'blog-posts',
      },
    }],
  }, {
    title: 'Layouts',
    items: [{
      title: 'Header Nav',
      htmlBefore: '<i class="material-icons">view_day</i>',
      to: {
        name: 'header-navigation',
      },
    }, {
      title: 'Icon Sidebar',
      htmlBefore: '<i class="material-icons">&#xE251;</i>',
      to: {
        name: 'icon-sidebar-nav',
      },
    }],
  },
  ];
}
