const passwordInput = document.querySelector('input#password');
const togglePasswordButton = document.querySelector('button#toggle-password');

if (passwordInput && togglePasswordButton) {
  togglePasswordButton.addEventListener('click', togglePassword);
}

function togglePassword () {
  let showText = togglePasswordButton.dataset.textShow;
  let hideText = togglePasswordButton.dataset.textHide;

  if (passwordInput.type === 'password') {
    passwordInput.type = 'text';
    togglePasswordButton.textContent = hideText;
  } else {
    passwordInput.type = 'password';
    togglePasswordButton.textContent = showText;
  }
}
