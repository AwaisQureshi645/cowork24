<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$host = 'localhost';
$dbname = 'coworker';
$username_db = 'root';
$password_db = '';

$conn = new mysqli($host, $username_db, $password_db, $dbname);
if ($conn->connect_error) {
  echo "<script>alert('Connection failed: " . $conn->connect_error . "');</script>";
  die("Connection failed: " . $conn->connect_error);
}

// Check user role
if (
  !isset($_SESSION['role']) ||
  ($_SESSION['role'] !== 'head' &&
    $_SESSION['role'] !== 'financehead' &&
    $_SESSION['role'] !== 'floorHost' &&
    $_SESSION['role'] !== 'manager')
) {
  echo "<script>alert('Access denied: insufficient privileges');</script>";
  header('Location: access_denied.php');
  exit();
}

// SQL query to fetch team names and corresponding Points of Contact
$sql = "SELECT teamName, PointofContact FROM team";
$result = $conn->query($sql);

$teams = [];
if ($result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $teams[] = [
      'teamName' => $row['teamName'],
      'PointofContact' => $row['PointofContact']
    ];
  }
}

$sql = "SELECT * FROM bookingsss";
$result = $conn->query($sql);

$bookings = [];
if ($result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $bookings[] = $row; // Add each row to the bookings array
  }
}


// echo json_encode(['Point of contratc' => $teams]);

// // Check if form is submitted
// if ($_SERVER["REQUEST_METHOD"] == "POST") {
//   // Get the submitted form values
//   $team = $_POST['team'];
//   $point_of_contract = $_POST['point_of_contract'];
//   $location = $_POST['location'];
//   $description = $_POST['description'];
//   $start = $_POST['start'];
//   $end = $_POST['end'];

//   // Prepare and bind the SQL statement to insert data into the bookings table
//   $stmt = $conn->prepare("INSERT INTO bookings (teamName, PointofContact, Location, Description, StartDate, EndDate) VALUES (?, ?, ?, ?, ?, ?)");
//   if ($stmt === false) {
//     echo "<script>alert('Error preparing statement: " . $conn->error . "');</script>";
//     exit();
//   }
//   echo "<script>alert('Booking saved successfully' .     $stmt->error);</script>";

//   $stmt->bind_param("ssssss", $team, $point_of_contract, $location, $description, $start, $end);

//   // Execute the statement
//   if ($stmt->execute()) {
//     echo "<script>alert('Booking saved successfully');</script>";
//   } else {
//     echo "<script>alert('Error saving booking: " . $stmt->error . "');</script>";
//   }

//   // Close the statement
//   $stmt->close();
// }

// Close the database connection
$conn->close();
?>




<!DOCTYPE html>
<html>

<head>
  <title>Google Calendar API Integration</title>
  <meta charset="utf-8" />
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 20px;
      background-color: #f4f4f9;
      color: #333;
      overflow-x: hidden !important;
    }

    #form-container {
      margin-top: 20px;
      padding: 20px;
      border: 1px solid #ddd;
      border-radius: 8px;
      background-color: #fff;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    h2 {
      color: #464646;
      margin-bottom: 20px;
    }

    label {
      display: block;
      margin-top: 10px;
      font-weight: bold;
    }

    input[type="text"],
    input[type="datetime-local"],
    select {
      width: calc(100% - 20px);
      padding: 10px;
      margin-top: 5px;
      border: 1px solid #ddd;
      border-radius: 4px;
      box-sizing: border-box;
    }

    input[type="text"] {
      background-color: #f9f9f9;
    }

    input[type="datetime-local"],
    select {
      background-color: #fff;
    }

    #content {
      margin-top: 20px;
      padding: 20px;
      background-color: #fff;
      border: 1px solid #ddd;
      border-radius: 8px;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
      display: none;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }

    table,
    th,
    td {
      border: 1px solid #ddd;
    }

    th,
    td {
      padding: 12px;
      text-align: left;
    }

    th {
      background-color: #464646 !important;
      color: white;
    }

    tr:nth-child(even) {
      background-color: #f2f2f2;
    }

    .event_container {
      width: 100%;
      max-width: 920px !important;
      margin: auto;
      background-color: white;
      border: 1px solid gray;
    }


    .event_container {
      margin: 20px auto;
      padding: 20px;
      max-width: 1000px;
      /* Restrict the width for large screens */
      overflow-x: auto;
      /* Ensure it scrolls horizontally on small screens */
      background-color: white;
      border: 1px solid #ddd;
      border-radius: 8px;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    /* Table styling */
    table {
      width: 100%;
      border-collapse: collapse;
      /* Ensure borders don't have gaps */
      margin-top: 20px;
    }

    th,
    td {
      border: 1px solid #ddd;
      padding: 10px;
      text-align: left;
      /* Align text to the left */
    }

    th {
      background-color: #007bff;
      color: white;
    }

    /* Alternating row colors */
    tr:nth-child(even) {
      background-color: #f2f2f2;
    }

    tr:hover {
      background-color: #e9e9e9;
    }

    /* Button styling */
    button {
      background-color: #ff4d4d;
      color: white;
      border: none;
      padding: 8px 12px;
      border-radius: 4px;
      cursor: pointer;
    }

    button:hover {
      background-color: #cc0000;
    }

    /* Link styling */
    a {
      color: #007bff;
      text-decoration: none;
    }

    a:hover {
      text-decoration: underline;
    }

    #create_event_button{
    margin-top: 10px;
    }
  </style>
</head>

<body>
  <h1>Google Calendar API</h1>
  <button id="signout_button" onclick="handleSignoutClick()">Sign Out</button>


  <div id="form-container" style="display: none;">
    <h2>Create or Edit Event</h2>
    <label for="summary">Summary:</label>
    <input type="text" id="summary" required />




    <label for="teamName">Team:</label>
    <select id="teamName" name="team" required>
      <option value="">--Select a Team--</option>
      <?php
      // Populate the dropdown options using the already fetched teams array
      foreach ($teams as $team) {
        echo '<option value="' . htmlspecialchars($team['teamName']) . '">' . htmlspecialchars($team['teamName']) . '</option>';
      }
      ?>
    </select>

    <!-- Point of Contact Input (readonly) -->
    <label for="point_of_contact">Point of Contact:</label>
    <input type="text" id="point_of_contact" name="point_of_contact" />
    <!-- Location Input -->
    <label for="location">Branch:</label>
    <select id="location" name="location" required>
      <option value="">--Select Branch--</option>
      <option value="Execute">Execute</option>
      <option value="Premium">Premium</option>
      <option value="I-10">I-10</option>
    </select>











    <label for="description">Description:</label>
    <input type="text" id="description" required />
    <label for="start">Start Time:</label>
    <input type="datetime-local" id="start" required onclick="this.showPicker();"/>
    <label for="end">End Time:</label>
    <input type="datetime-local" id="end" required onclick="this.showPicker();"/>
    <label for="room_type">Room Type:</label>
    <select id="room_type" required>
      <option value="meeting">Meeting Room</option>
      <option value="huddle">Huddle Room</option>
    </select>
    <label for="room">Room:</label>
    <select id="room" required></select>
    <button id="create_event_button">Create Event</button>
    <button id="update_event_button" style="display: none;">Update Event</button>

  </div>



  <h2>Upcoming Events</h2>
  <div class="event_container">


    <table>
      <tr>

        <th>Room Type</th>
        <th>Start Time</th>
        <th>End Time</th>
        <th>Team Name</th>
        <th>Location</th>
        <th>Description</th>
        <th>Point of Contact</th>
        <th>Summary</th>
        <th>Actions</th>
      </tr>

      <?php

      $host = 'localhost';
      $dbname = 'coworker';
      $username_db = 'root';
      $password_db = '';

      $conn = new mysqli($host, $username_db, $password_db, $dbname);
      if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
      }


      $sql = "SELECT * FROM bookingsss";
      $result = $conn->query($sql);

      if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
          echo "<tr>
                   
                    <td>{$row['room_type']}</td>
                    <td>{$row['start_time']}</td>
                    <td>{$row['end_time']}</td>
                    <td>{$row['team_name']}</td>
                    <td>{$row['location']}</td>
                    <td>{$row['description']}</td>
                    <td>{$row['point_of_contact']}</td>
                    <td>{$row['summary']}</td>
                    <td>
                
                 <button  onclick=\"deleteEvent('{$row['event_id']}'); return false;\">Delete</a>

                    </td>
                  </tr>";
        }
      } else {
        echo "<tr><td colspan='10'>No records found</td></tr>";
      }
      ?>
    </table>



  </div>




  <div id="content"></div>

  <script src="https://apis.google.com/js/api.js" onload="gapiLoaded()" async defer></script>
  <script src="https://accounts.google.com/gsi/client" onload="gisLoaded()" async defer></script>
  <script type="text/javascript">
    const CLIENT_ID = "269162590647-alh83bvusko86o4svfge4lsl5abn1c08.apps.googleusercontent.com";
    const API_KEY = "AIzaSyCciXrg_icHlFR-hn-ROqWp9KP4QW0Z5eE";
    const DISCOVERY_DOC = "https://www.googleapis.com/discovery/v1/apis/calendar/v3/rest";
    const SCOPES = "https://www.googleapis.com/auth/calendar";

    let tokenClient;
    let gapiInited = false;
    let gisInited = false;
    let currentEventId = null;

    function gapiLoaded() {
      gapi.load("client", initializeGapiClient);
    }

    async function initializeGapiClient() {
      await gapi.client.init({
        apiKey: API_KEY,
        discoveryDocs: [DISCOVERY_DOC],
      });
      gapiInited = true;
      checkIfAuthorized();
    }

    function gisLoaded() {
      tokenClient = google.accounts.oauth2.initTokenClient({
        client_id: CLIENT_ID,
        scope: SCOPES,
        callback: handleAuthResponse,
      });
      gisInited = true;
      checkIfAuthorized();
    }

    // Check if user is already authorized
    function checkIfAuthorized() {
      const token = sessionStorage.getItem('google_token');
      if (token) {
        gapi.client.setToken({
          access_token: token
        });
        document.getElementById("form-container").style.display = "block";
        listUpcomingEvents();
        loadRooms();
      } else if (gapiInited && gisInited) {
        // If not authorized, auto-trigger the authorization
        handleAuthClick();
      }
    }

    function handleAuthResponse(resp) {
      if (resp.error !== undefined) {
        console.error("Authorization error:", resp.error);
        document.getElementById("content").innerText = `Authorization error: ${resp.error}`;
        return;
      }

      sessionStorage.setItem('google_token', resp.access_token); // Store token in sessionStorage

      document.getElementById("form-container").style.display = "block";
      listUpcomingEvents();
      loadRooms();
    }

    function handleAuthClick() {
      if (gapi.client.getToken() === null) {
        tokenClient.requestAccessToken({
          prompt: "consent"
        });
      } else {
        tokenClient.requestAccessToken({
          prompt: ""
        });
      }
    }

    // No signout required since we want the authorization to persist
    function handleSignoutClick() {
      sessionStorage.removeItem('google_token');
      const token = gapi.client.getToken();
      if (token !== null) {
        google.accounts.oauth2.revoke(token.access_token);
        gapi.client.setToken("");
        document.getElementById("form-container").style.display = "none";
        alert("Signed out. You will need to authorize again.");
      }
    }

    async function listUpcomingEvents() {
      let response;
      try {
        const request = {
          calendarId: "primary",
          timeMin: new Date().toISOString(),
          showDeleted: false,
          singleEvents: true,
          maxResults: 50,
          orderBy: "startTime",



        };
        response = await gapi.client.calendar.events.list(request);
        console.log('API Response:', response);



      } catch (err) {
        alert(err.message)
        console.error("Error fetching events:", err);
        document.getElementById("content").innerText = `Error fetching events: ${err.message}`;
        return;
      }

      const events = response.result.items;
      if (!events || events.length === 0) {
        document.getElementById("content").innerText = "No upcoming events found.";
        return;
      }

      const contentDiv = document.getElementById("content");
      contentDiv.innerHTML = "<h2>Upcoming Events</h2>";

      const table = document.createElement("table");
      const thead = document.createElement("thead");
      const tbody = document.createElement("tbody");

      thead.innerHTML = `
        <tr>
          <th>Summary</th>
          <th>Start Time</th>
          <th>End Time</th>
           <th>Team Name</th>
                   <th>Team Name</th>
                        <th>Point of contact</th>
          <th>Actions</th>
        </tr>
      `;




      const bookings = <?php echo json_encode($bookings); ?>;

      console.log("this is a boking data", bookings);






      events.forEach((event) => {
        const tr = document.createElement("tr");
        const start = event.start.dateTime || event.start.date;
        const end = event.end.dateTime || event.end.date;

        console.log(event)

        tr.innerHTML = `
          <td>${event.summary}</td>
          <td>${start}</td>
          <td>${end}</td>
          <td>${event.location}</td>
             <td>${event.teamName}</td>
             <td>${event.pointOfContact}</td>
          <td>
            <button onclick="editEvent('${event.id}')">Edit</button>
            <button onclick="deleteEvent('${event.id}')">Delete</button>
          </td>
        `;
        tbody.appendChild(tr);
      });

      table.appendChild(thead);
      table.appendChild(tbody);
      contentDiv.appendChild(table);
    }

    async function loadRooms() {
      const roomTypeSelect = document.getElementById("room_type");
      const roomSelect = document.getElementById("room");

      roomTypeSelect.addEventListener("change", async () => {
        const roomType = roomTypeSelect.value;
        let rooms;

        try {
          const response = await fetch(`rooms.php?type=${roomType}`);
          if (!response.ok) {
            throw new Error("Network response was not ok");
          }
          rooms = await response.json();
        } catch (err) {
          console.error("Error loading rooms:", err);
          return;
        }

        roomSelect.innerHTML = "";

        rooms.forEach((room) => {
          const option = document.createElement("option");
          option.value = room.id;
          option.textContent = room.name;
          roomSelect.appendChild(option);
        });
      });

      roomTypeSelect.dispatchEvent(new Event("change"));
    }

    document.getElementById("create_event_button").addEventListener("click", createEvent);
    async function createEvent() {
      const summary = document.getElementById("summary").value;
      const teamName = document.getElementById("teamName");
      const pointOfContact = document.getElementById("point_of_contact").value;
      const location = document.getElementById("location").value;
      const description = document.getElementById("description").value;
      const start = document.getElementById("start").value;
      const end = document.getElementById("end").value;
      const roomType = document.getElementById("room_type").value;
      const roomId = document.getElementById("room").value;
      const roomName = document.getElementById("room").selectedOptions[0].text;

      if (!summary || !location || !description || !start || !end || !roomType || !roomId) {
        alert("Please fill in all fields.");
        return;
      }

      try {
        const availabilityResponse = await fetch('/cowork/check_availability.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            roomId: roomId,
            startTime: new Date(start).toISOString(),
            endTime: new Date(end).toISOString(),
            roomType: roomType
          })
        });

        if (!availabilityResponse.ok) {
          throw new Error("Network response was not ok");
        }

        const availabilityData = await availabilityResponse.json();
        console.log("Room availability response:", availabilityData);

        if (availabilityData.available) {
          const event = {
            summary: summary,
            teamName: teamName,
            point_of_contact: pointOfContact,
            location: location,
            description: `${description}\nRoom Type: ${roomType}\nRoom: ${roomName}`,
            start: {
              dateTime: new Date(start).toISOString(),
              timeZone: "Asia/Karachi",
            },
            end: {
              dateTime: new Date(end).toISOString(),
              timeZone: "Asia/Karachi",
            },
          };

          try {
            const eventResponse = await gapi.client.calendar.events.insert({
              calendarId: "primary",
              resource: event,
            });

            const eventId1 = eventResponse.result.id;
            const jsonData = JSON.stringify({
              summary: summary,
              eventId: eventId1,
              roomId: roomId,
              roomType: roomType,
              teamName: teamName.value,
              pointOfContact: pointOfContact,
              location: location,
              startTime: new Date(start).toISOString(),
              endTime: new Date(end).toISOString(),
              description: description
            });
            await fetch('http://localhost/cowork/save_booking.php', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json'
              },
              body: jsonData
            });

            console.log("form data", jsonData);
            alert("Event created successfully: " + eventResponse.result);
            listUpcomingEvents();
          } catch (err) {
            console.error("Error creating event:", err);
            alert("Error creating event: " + err.message);
          }
        } else {
          let alternativeRoomsHtml = "<h3>Selected room is not available. Please choose another room:</h3><ul>";

          if (Array.isArray(availabilityData.alternatives)) {
            availabilityData.alternatives.forEach(room => {
              alternativeRoomsHtml += `<li>${room.name} (ID: ${room.id})</li>`;
            });
          } else {
            alternativeRoomsHtml += "<li>No alternative rooms available.</li>";
          }

          alternativeRoomsHtml += "</ul>";
          document.getElementById("content").innerHTML = alternativeRoomsHtml;
        }
      } catch (err) {
        console.error("Error checking room availability:", err);
        alert("Error checking room availability: " + err.message);
      }
    }

    function formatDateForInput(date) {
      const d = new Date(date);
      const year = d.getFullYear();
      const month = String(d.getMonth() + 1).padStart(2, '0');
      const day = String(d.getDate()).padStart(2, '0');
      const hours = String(d.getHours()).padStart(2, '0');
      const minutes = String(d.getMinutes()).padStart(2, '0');
      return `${year}-${month}-${day}T${hours}:${minutes}`;
    }

    async function editEvent(eventId) {
      try {
        const response = await gapi.client.calendar.events.get({
          calendarId: "primary",
          eventId: eventId,
        });
        const event = response.result;

        document.getElementById("summary").value = event.summary;
        document.getElementById("location").value = event.location;
        document.getElementById("description").value = event.description;

        const startDate = formatDateForInput(event.start.dateTime || event.start.date);
        const endDate = formatDateForInput(event.end.dateTime || event.end.date);

        document.getElementById("start").value = startDate;
        document.getElementById("end").value = endDate;

        document.getElementById("create_event_button").style.display = "none";
        document.getElementById("update_event_button").style.display = "block";
        currentEventId = eventId;
      } catch (err) {
        console.error("Error fetching event for editing:", err);
        alert("Failed to load event for editing: " + err.message);
      }
    }

    async function deleteEvent(eventId) {
      try {
        await gapi.client.calendar.events.delete({
          calendarId: "primary",
          eventId: eventId,
        });

        const response = await fetch('delete_bookings.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            eventId: eventId
          }),
        });
        console.log("data delete from tables", response.status)

        if (!response.ok) {
          throw new Error('Failed to delete event from database');
        }

        alert("Event deleted successfully!");
        listUpcomingEvents();
      } catch (err) {
        console.error("Error deleting event:", err);
        alert("Failed to delete event: " + err.message);
      }
    }

    async function syncEventWithDatabase(event, roomId, roomType) {
      try {
        const response = await fetch("save_booking.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/json"
          },
          body: JSON.stringify({




            roomId: roomId,
            eventId: currentEventId,
            roomType: roomType,
            startTime: event.start.dateTime,
            endTime: event.end.dateTime,
            teamName: teamName,
            pointOfContact: pointOfContact,
            location: location,
            description: description
          }),
        });

        if (!response.ok) {
          const text = await response.text();
          console.error("Server error:", text);
          throw new Error("Failed to sync with database");
        }

        const result = await response.json();
        console.log(result.success || "Event synced successfully with the database.");
      } catch (err) {
        console.error("Error syncing event with database:", err.message);
      }
    }
  </script>

  <script src="https://apis.google.com/js/api.js" onload="gapiLoaded()"></script>
  <script src="https://accounts.google.com/gsi/client" async defer onload="gisLoaded()"></script>
  <script type="text/javascript">
    // Pass PHP teams array to JavaScript
    const teams = <?php echo json_encode($teams); ?>;
  </script>


  </script>

  <script src="https://apis.google.com/js/api.js" onload="gapiLoaded()"></script>
  <script src="https://accounts.google.com/gsi/client" async defer onload="gisLoaded()"></script>

  <script type="text/javascript">
    // Get references to the team select dropdown and the point of contact input field
    const teamSelect = document.getElementById('teamName'); // Use 'teamName' since that's the ID in your HTML
    const pointOfContactInput = document.getElementById('point_of_contact');

    // Add event listener to update the point of contact when a team is selected
    teamSelect.addEventListener('change', function() {
      const selectedTeam = teamSelect.value;

      // Find the selected team's point of contact from the teams array
      const teamData = teams.find(team => team.teamName === selectedTeam);

      // If the team is found, display the Point of Contact, otherwise clear the input
      if (teamData) {
        pointOfContactInput.value = teamData.PointofContact;
      } else {
        pointOfContactInput.value = ''; // Clear input if no team is selected
      }
    });
  </script>


</body>

</html>