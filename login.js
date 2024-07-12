function login() {
    window.location.href = 'login.html';
}

$(document).ready(function() {
    $('#loginForm').submit(function(event) {
        event.preventDefault();

        let loginData = {
            email: $('#email').val(),
            password: $('#password').val()
        };

        $.ajax({
            url: 'http://localhost:3000/project/Static/login.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(loginData),
            dataType: 'json',
            success: function(response) {
                if (response.exists && response.status === 'success') {
                    localStorage.setItem('userEmail', response.user.email);
                    localStorage.setItem('userPassword', response.user.password);
                    localStorage.setItem('userName', response.user.name); // Store the user's name
                    localStorage.setItem('isLoggedIn', 'true'); // Set the logged-in flag
                    $('#result').text('Login successful!');
                    setTimeout(function() {
                        window.location.href = 'homepage.html';
                    }, 2000);
                } else {
                    $('#result').text('Login failed: ' + response.message);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#result').text('An error occurred: ' + textStatus + ' ' + errorThrown);
                console.log('Error details:', jqXHR.responseText);
            }
        });
    });

    // Check if the user is logged in and update the login button on page load
    let isLoggedIn = localStorage.getItem('isLoggedIn');
    let userName = localStorage.getItem('userName');

    if (isLoggedIn === 'true' && userName) {
        $('#loginButton').text(userName).hide();
    }
});
