console.log("entered signup.js");
$(document).ready(function() {
    $('#signupForm').submit(function(event) {
        event.preventDefault();

        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(successCallback, errorCallback);
        } else {
            console.log("Geolocation is not supported by this browser.");
            // Proceed without location
            submitForm();
        }
        
        function successCallback(position) {
            var latitude = position.coords.latitude;
            var longitude = position.coords.longitude;
            console.log("Latitude: " + latitude + ", Longitude: " + longitude);
            
            // Include location data in the form data and submit
            submitForm(latitude, longitude);
        }
        
        function errorCallback(error) {
            console.log("Error occurred. Error code: " + error.code);
            // Proceed without location
            submitForm();
        }

        function submitForm(latitude, longitude) {
            let signupData = {
                name: $('#name').val(),
                email: $('#email').val(),
                phone: $('#phone').val(),
                address: $('#address').val(),
                password: $('#password').val(),
                latitude: latitude,
                longitude: longitude
            };

            $.ajax({
                url: 'http://localhost:3000/project/Static/signup.php',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(signupData),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('#result').text('Sign up successful!');
                        setTimeout(function() {
                            window.location.href = 'homepage.html';
                        }, 2000);
                    } else {
                        $('#result').text('Sign up failed: ' + response.error);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    $('#result').text('An error occurred: ' + textStatus + ' ' + errorThrown);
                    console.log('Error details:', jqXHR.responseText);
                }
            });
        }
    });
});
