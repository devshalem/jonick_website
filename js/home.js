function clearForm(event) {
  event.preventDefault(); // Prevents default form submission
  const form = document.getElementById('login-form');
  form.reset(); // Clears the form input fields
}

$(document).ready(function(){
    $('.modal').modal(); // Initialize the modal

    // Open login modal when "Login" button is clicked
    $('#login-button').on('click', function(e) {
        e.preventDefault(); // Prevent default anchor click behavior
        $('#login-modal').modal('open');
    });

    // Handle the login form submission
  // ... (previous code in home.js) ...

// Handle the login form submission
$('#login-form').on('submit', async function(e) {
    e.preventDefault();

    const email = $('#email').val();
    const password = $('#password').val();

    if (!email || !password) {
        M.toast({html: 'Please enter both email and password.', classes: 'red'});
        return;
    }

    try {
        const response = await fetch('api/routes/auth/login.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `email=${encodeURIComponent(email)}&password=${encodeURIComponent(password)}`
        });

        const result = await response.json();

        if (result.status === 'success') {
            M.toast({html: result.message, classes: 'green'});
            // Store token and user details in localStorage
            localStorage.setItem('jwt_token', result.token);
            localStorage.setItem('user_id', result.user.ID);
            localStorage.setItem('user_name', result.user.NAME);
            localStorage.setItem('user_email', result.user.EMAIL);
            localStorage.setItem('user_role', result.user.ROLE);

            $('#login-modal').modal('close'); // Close the login modal
            $(this).trigger('reset'); // Clear the form

            // Redirect for general users (admins will use admin_login.html now)
            // if (result.user.ROLE === 'admin') { // REMOVE THIS IF/ELSE BLOCK FOR REDIRECTION
            //     window.location.href = 'admin_dashboard.html';
            // } else {
            //     window.location.href = 'index.html'; // Or 'user_dashboard.html'
            // }
            window.location.href = 'index.html'; // Direct non-admin logins back to home or a user dashboard

        } else {
            M.toast({html: result.message || 'Login failed. Please try again.', classes: 'red'});
        }
    } catch (error) {
        console.error('Login error:', error);
        M.toast({html: 'An error occurred during login. Please try again later.', classes: 'red'});
    }
});

// ... (rest of code in home.js) ...

    $('.dropdown-trigger').dropdown();
    $('.sidenav').sidenav();

    // Open services sidenav
    $('.sidenav-trigger[data-target="services-sidenav"]').on('click', function() {
        $('#services-sidenav').sidenav('open');
        $('#mobile-demo').sidenav('close'); // Close main sidenav
    });

    // Open main sidenav when "Back" is clicked
    $('.sidenav-close').on('click', function() {
        $('#services-sidenav').sidenav('close');
        $('#mobile-demo').sidenav('open'); // Reopen the main sidenav
    });

    // Close the main sidenav completely when the close icon is clicked
    $('.sidenav-close-icon').on('click', function() {
        $('.sidenav').sidenav('close');
    });

    // LOGIN (Social Logins - Keep as is, unless backend integration is needed)
    // Facebook Login
    document.getElementById('fb-login-btn').onclick = function() {
        FB.login(function(response) {
            if (response.authResponse) {
                console.log('Welcome! Fetching your information.... ');
                FB.api('/me', { locale: 'en_US', fields: 'name, email' }, function(response) {
                    console.log('Good to see you, ' + response.name + '.');
                    // You can send the user data to your server here for processing
                    // For social logins, you'd typically send the token/user data to your backend
                    // for verification and to generate your own JWT.
                });
            } else {
                console.log('User cancelled login or did not fully authorize.');
            }
        }, {scope: 'public_profile,email'});
    };

    // Google Login
    function onGoogleSignIn(googleUser) {
        var profile = googleUser.getBasicProfile();
        console.log('ID: ' + profile.getId());
        console.log('Name: ' + profile.getName());
        console.log('Image URL: ' + profile.getImageUrl());
        console.log('Email: ' + profile.getEmail());
        // You can send the user data to your server here for processing
        // Similar to Facebook, send this data to your backend for verification
        // and to generate your own JWT.
    }

    // Google SDK onload function (from index.html script tag)
    // This function needs to be globally accessible or called from within the script.
    // Ensure `onLoad` is accessible if `index.html` calls it directly in the script tag.
    // Or, move its content inside $(document).ready for a more contained approach.
    // For now, assuming it's correctly called from index.html
    window.onLoad = function() {
        gapi.load('auth2', function() {
            gapi.auth2.init({
                client_id: 'YOUR_GOOGLE_CLIENT_ID.apps.googleusercontent.com' // Replace with your Google Client ID
            }).then(function() {
                document.getElementById('google-login-btn').onclick = function() {
                    gapi.auth2.getAuthInstance().signIn().then(onGoogleSignIn);
                };
            });
        });
    };
});