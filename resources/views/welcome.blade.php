<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Travel Booking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .calendar {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 10px;
            margin-top: 20px;
        }
        .day {
            background-color: #fff;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 10px;
            text-align: center;
            transition: all 0.3s ease;
        }
        .day:hover {
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .error-message {
            color: #dc3545;
        }
        .table {
            background-color: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .table th {
            background-color: #007bff;
            color: white;
        }
        .table tbody tr:hover {
            background-color: #f1f8ff;
        }
        #available-times {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        .time-btn {
            flex: 1 0 calc(33.333% - 10px);
            max-width: calc(33.333% - 10px);
        }
        @media (max-width: 768px) {
            .time-btn {
                flex: 1 0 calc(50% - 10px);
                max-width: calc(50% - 10px);
            }
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4 text-center">Travel Booking</h1>
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h2 class="mb-0">Available Travel Modes</h2>
                    </div>
                    <div class="card-body">
                        <table class="table table-hover" id="travel-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Travel Mode</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Travel modes will be populated here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h2 class="mb-0">Travel Dates</h2>
                    </div>
                    <div class="card-body">
                        <div id="calendar-container"></div>
                        <div id="error-message" class="error-message mt-2"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h2 class="mb-0">Available Times</h2>
                    </div>
                    <div class="card-body">
                        <div id="available-times"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div id="selection-form" class="card" style="display: none;">
                    <div class="card-header bg-success text-white">
                        <h2 class="mb-0">Your Selection</h2>
                    </div>
                    <div class="card-body">
                        <p id="selected-travel-mode" class="card-text"></p>
                        <p id="selected-date" class="card-text"></p>
                        <p id="selected-time" class="card-text"></p>
                        <button class="btn btn-primary" id="save-btn">Save Booking</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
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
                    console.error('Error fetching travel data');
                    $('#travel-table tbody').html('<tr><td colspan="3" class="text-center">Error fetching travel data</td></tr>');
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
                calendarContainer.append('<p class="text-center">No travel dates found for this travel mode.</p>');
                return;
            }

            const calendar = $('<div class="calendar"></div>');
            dates.forEach(date => {
                calendar.append(`<div class="day">
                                    ${date.traveldate}
                                    <button class="btn btn-sm btn-outline-primary mt-2 fetch-time-btn" data-id="${date.id}" data-date="${date.traveldate}">Get Times</button>
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
                availableTimesContainer.append('<p class="text-center">No available times found for this date.</p>');
                return;
            }

            times.forEach(time => {
                availableTimesContainer.append(`<button class="btn btn-outline-secondary select-time-btn time-btn" data-time="${time.traveltime}">${time.traveltime}</button>`);
            });
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
                    },
                    error: function(xhr, status, error) {
                        alert('Error saving travel info: ' + error);
                    }
                });
            });
        });
    </script>
</body>
</html>