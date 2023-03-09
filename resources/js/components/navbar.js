const navbar = document.getElementById('navbar');

const navbarMenuButton = (navbar) ? document.getElementById('navbar-menu-button') : null;
if (navbarMenuButton) {
  navbarMenuButton.addEventListener('click', () => {
    navbar.getElementsByClassName('navbar-collapse')[0].classList.toggle(
      'show');
  });
}

const navbarUserButton = (navbar) ? document.getElementById(
  'navbar-user-button') : null;
if (navbarUserButton) {
  navbarUserButton.addEventListener('click', () => {
    navbar.getElementsByClassName('dropdown-menu')[0].classList.toggle(
      'show');
  });
}

