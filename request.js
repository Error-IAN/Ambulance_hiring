console.log("Entered request.js");

$(document).ready(function() {
    $('#requestForm').submit(function(event) {
        event.preventDefault();

        // Get stored email and password
        let email = localStorage.getItem('userEmail');
        let password = localStorage.getItem('userPassword');

        if (!email || !password) {
            $('#result').text('User not authenticated');
            return;
        }

        // Get geolocation if available
        if (navigator.geolocation) {
            console.log("Getting location...");
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

        function submitForm(latitude = null, longitude = null) {
            var requestData = {
                email: email,
                password: password,
                location: $('#location').val(),
                reason: $('#reason').val(),
                latitude: latitude,
                longitude: longitude
            };

            $.ajax({
                url: 'http://localhost:3000/project/Static/request.php',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(requestData),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('#result').text('Request submitted successfully!');
                        // Show modal when ambulance is assigned
                        if (response.ambulanceAssigned) {
                            $('#modalMessage').text('Ambulance is on its way!');
                            $('#myModal').modal('show');
                        }
                        setTimeout(function() {
                            window.location.href = 'homepage.html';
                        }, 2000);
                    } else {
                        $('#result').text('Request submission failed: ' + response.error);
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
