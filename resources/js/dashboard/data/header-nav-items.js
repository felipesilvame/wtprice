export default function () {
  return [{
    title: 'Dashboards',
    htmlBefore: '<i class="material-icons">&#xE2C7;</i>',
    items: [{
      title: 'Analytics',
      to: {
        name: 'analytics',
      },
    }, {
      title: 'Store',
      to: {
        name: 'ecommerce',
      },
    }, {
      title: 'Blog',
      to: {
        name: 'blog-overview',
      },
    }],
  }, {
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
  }, {
    title: 'Components',
    htmlBefore: '<i class="material-icons">view_module</i>',
    items: [{
      title: 'Overview',
      to: {
        name: 'components-overview',
      },
    }, {
      title: 'Blog Posts',
      to: {
        name: 'blog-posts',
      },
    }],
  }, {
    title: 'File Managers',
    htmlBefore: '<i class="material-icons">&#xE2C7;</i>',
    items: [{
      title: 'File Manager - List',
      to: {
        name: 'file-manager-list',
      },
    }, {
      title: 'File Manager - Cards',
      to: {
        name: 'file-manager-cards',
      },
    }],
  }, {
    title: 'Transactions',
    htmlBefore: '<i class="material-icons"></i>',
    to: {
      name: 'transaction-history',
    },
  }, {
    title: 'User Account',
    htmlBefore: '<i class="material-icons">&#xE8B9;</i>',
    items: [{
      title: 'User Profile',
      to: {
        name: 'user-profile',
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
      title: 'Forgot Password',
      to: {
        name: 'forgot-password',
      },
    }, {
      title: 'Reset Password',
      to: {
        name: 'reset-password',
      },
    }],
  }, {
    title: 'Errors',
    htmlBefore: '<i class="material-icons">error</i>',
    to: {
      name: 'errors',
    },
  }];
}
