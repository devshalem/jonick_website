document.addEventListener("DOMContentLoaded", function () {
  const signupForm = document.getElementById("email-signup-form");

  function showToast(message, type = 'green') {
    M.toast({ html: message, classes: type });
  }

  signupForm.addEventListener("submit", async function (event) {
    event.preventDefault();

    const name = event.target.name.value;
    const email = event.target.email.value;
    const password = event.target.password.value;
    const phone = event.target.phone.value;

    if (!name || !email || !password) {
      showToast('Please fill in all required fields (Name, Email, Password).', 'red');
      return;
    }

    if (password.length < 8) {
      showToast('Password must be at least 8 characters long.', 'red');
      return;
    }

    const formData = new URLSearchParams();
    formData.append('name', name);
    formData.append('email', email);
    formData.append('password', password);
    formData.append('phone', phone);

    try {
      const response = await fetch('api/routes/auth/register.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: formData.toString()
      });

      if (!response.ok) {
        throw new Error('Network response was not ok');
      }

      let result;
      const contentType = response.headers.get('content-type');
      if (contentType && contentType.includes('application/json')) {
        result = await response.json();
      } else {
        const text = await response.text();
        result = { status: 'error', message: 'Invalid server response: ' + text };
      }

      if (result.status === 'success') {
        showToast(result.message || 'Registration successful!', 'green');
        signupForm.reset();
        M.updateTextFields();
        setTimeout(() => {
          window.location.href = "index.html";
        }, 2000);
      } else {
        const message = result.errors ? Object.values(result.errors).join('<br>') : result.message || 'Registration failed.';
        showToast(message, 'red');
      }
    } catch (error) {
      console.error("Error during email sign-up:", error);
      showToast('An unexpected error occurred. Please try again later.', 'red');
    }
  });

  window.signUpWithGoogle = function () {
    showToast('Google Sign Up not implemented.', 'blue');
  };

  window.signUpWithFacebook = function () {
    showToast('Facebook Sign Up not implemented.', 'blue');
  };
});