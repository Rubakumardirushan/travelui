<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Travel API Data</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>

        .calendar {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 5px;
            margin-top: 20px;
            margin-left: 230px;
        }
        .time{
            margin-left: 200px;
        }
        .day {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: center;
            position: relative;
        }
        
        .info-data{
             margin-left: 200px;
        }
        .error-message {
            color: red;
            margin-left: 390px;
        }
        
        .table {
            border-radius: 0.5rem;
            overflow: hidden;
        }
        .table th {
            background-color: #007bff;
            color: white;
        }
        .table tbody tr:hover {
            background-color: #f1f1f1;
        }
        .table tbody tr {
            transition: background-color 0.3s;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">Travel Data</h1>
        <table class="table table-striped table-hover" id="travel-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Travel Mode</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            
            </tbody>
        </table>
        
        <div id="calendar-container" class="mt-5"></div> 
        <div class="error-message" id="error-message"></div> 
        <div id="available-times" class="time"></div>
        
        
        <div id="selection-form" class="info-data" style="display: none; margin-left:400px">
            <h5>Your Selection</h5>
            <p id="selected-travel-mode"></p>
            <p id="selected-date"></p>
            <p id="selected-time"></p>
            <button class="btn btn-success" id="save-btn">Save</button>
        </div>
    </div>

    <script>
        let selectedTravelMode = '';
        let selectedDate = '';
        let selectedTime = '';

        function fetchTravelData() {
            $.ajax({
                url: 'http://127.0.0.1:8000/api/travel',
                method: 'GET',
                success: function(data) {
                    let rows = '';
                    $.each(data, function(index, travel) {
                        rows += `<tr>
                                    <td>${travel.id}</td>
                                    <td>${travel.name}</td>
                                    <td><button class="btn btn-primary check-btn" data-id="${travel.id}">Check</button></td>
                                 </tr>`;
                    });
                    $('#travel-table tbody').html(rows);
                },
                error: function() {
                    console.error('Error fetching data');
                }
            });
        }

        function fetchTravelDates(id) {
            $.ajax({
                url: `http://127.0.0.1:8000/api/traveldate/${id}`,
                method: 'GET',
                success: function(dates) {
                    displayCalendar(dates);
                    $('#error-message').text(''); 
                },
                error: function(jqXHR) {
                    const response = jqXHR.responseJSON;
                    if (response) {
                        
                        $('#error-message').text(response.message);
                    } else {
                        $('#error-message').text('An unknown error occurred.');
                    }
                    $('#calendar-container').empty(); 
                    $('#available-times').empty(); 
                }
            });
        }

        function displayCalendar(dates) {
            const calendarContainer = $('#calendar-container');
            calendarContainer.empty(); 

            if (dates.length === 0) {
                calendarContainer.append('<p>No travel dates found for this travel mode.</p>');
                return;
            }

            const calendar = $('<div class="calendar"></div>');
            dates.forEach(date => {
                calendar.append(`<div class="day">
                                    ${date.traveldate} 
                                    <button class="btn btn-secondary mt-2 fetch-time-btn" data-id="${date.id}" data-date="${date.traveldate}">Get Times</button>
                                  </div>`);
            });

            calendarContainer.append(calendar); 
        }

        function fetchTravelTimes(dateId) {
            
            $('#available-times').empty();  

            $.ajax({
                url: `http://127.0.0.1:8000/api/traveltime/${dateId}`,
                method: 'GET',
                success: function(times) {
                    displayAvailableTimes(times);
                },
                error: function() {
                    $('#available-times').html('<p class="error-message">Error fetching available times.</p>');
                }
            });
        }

        function displayAvailableTimes(times) {
            const availableTimesContainer = $('#available-times');
            availableTimesContainer.empty(); 

            if (times.length === 0) {
                availableTimesContainer.append('<p>No available times found for this date.</p>');
                return;
            }

            const timeList = $('<div class="btn-group-vertical"></div>'); 
            times.forEach(time => {
                timeList.append(`<button class="btn btn-info mt-2 time select-time-btn" data-time="${time.traveltime}">${time.traveltime}</button>`); // Each time is a button
            });

            availableTimesContainer.append(timeList); 
        }

        $(document).ready(function() {
            
            fetchTravelData();

        
            setInterval(fetchTravelData, 60000);

        
            $(document).on('click', '.check-btn', function() {
                const id = $(this).data('id');
                selectedTravelMode = $(this).closest('tr').find('td:eq(1)').text(); 
                fetchTravelDates(id);
            });

            
            $(document).on('click', '.fetch-time-btn', function() {
                const dateId = $(this).data('id');
                selectedDate = $(this).data('date'); 

            

                fetchTravelTimes(dateId);
            });

            
            $(document).on('click', '.select-time-btn', function() {
                selectedTime = $(this).data('time'); 

                
                $('.select-time-btn').hide();

                
                $('#selected-travel-mode').text(`Travel Mode: ${selectedTravelMode}`);
                $('#selected-date').text(`Date: ${selectedDate}`);
                $('#selected-time').text(`Time: ${selectedTime}`);
                $('#selection-form').show(); 
            });

        
            $(document).on('click', '#save-btn', function() {
            
                const postData = {
                    travel_mode: selectedTravelMode,
                    travel_date: selectedDate,
                    travel_time: selectedTime
                };

                $.ajax({
                    url: 'http://127.0.0.1:8001/api/travelinfo',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify(postData),
                    success: function(response) {
                        alert('Travel info saved successfully!');
                        $('#selection-form').hide(); 
                        $('#available-times').empty(); 
                        $('.fetch-time-btn').show(); 
                        $('.select-time-btn').show(); 
                    },
                    error: function(xhr, status, error) {
                        alert('Error saving travel info: ' + error);
                    }
                });
            });
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
