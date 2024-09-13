<?php  
session_start();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="cowork-logo.PNG">
    <title>Dashboard</title>
    <style>
        * {
            font-family: Helvetica;
            padding: 0;
            margin: 0;
            box-sizing: border-box;
        }

        body,
        html {
            height: 100%;
            margin: 0;
        }

        .container {
            display: flex;
            height: 100%;
        }

        .sidemenu {
            position: fixed;
            background-color: #2F3645;
            color: white;
            width: 250px;
            height: 100vh; /* Full viewport height */
            overflow-y: auto; /* Enable vertical scrolling */
        }

        .logo {
            width: 100%;
            display: flex;
            justify-content: center;
            padding: 20px;
            font-size: 40px;
        }

        .menu {
            font-weight: 800;
            width: 100%;
            font-size: larger;
        }

        .menushow {
            display: flex;
            align-items: center;
            font-size:large;
        }

        .menucontrol {
            display: flex;
            /* flex-direction: column; */
            justify-content: space-between;
            padding: 15px;
            cursor: pointer;
        }

        .menushow p {
            padding-left: 10px;
        }

        .icons {
            font-size: 20px;
            cursor: pointer;
        }

        .menuitem ul {
            height: 0;
            overflow: hidden;
            list-style-type: none;
            transition: height 0.3s ease;
            background-color:#088395;
        }

        .menuitem ul li {
            padding: 10px 20px;
            font-size: 14px;
        }

        .menuitem.active ul {
            height: auto;
        }

        .menuitem ul li a {
            color: white;
            text-decoration: none;
        }

        .menuitem ul li a:hover {
            text-decoration: underline;
        }

        .content {
            margin-left: 250px;
            padding: 10px;
            width: calc(100% - 250px);
            height: 100vh;
            background-size: cover;
            background-position: center;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow-x: auto;
        }

        .content iframe {
            width: 100%;
            height: 100%;
            border: double;
            overflow: auto;
        }
        .logout_btn_dashboard{
            width: 7rem;
            display: flex;
            margin: auto;
            padding: 10px 10px;
            background-color: #dc3545;
            color: white;
            text-align: center;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            border: none;
            cursor: pointer;
            justify-content: center;
     
        }
        .logout_btn_dashboard a{
            text-decoration: none;
            color: white;
            text-align: center;

        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
</head>

<body>
    <div class="container">
        <div class="sidemenu">
            <div class="logo">
                <i class="fa-brands fa-joomla"></i>
            </div>

            <div class="menu">
                <div class="menuitem">
                    <div class="menucontrol " >
                     <div style="display:flex;Flex-direction:column">
                       
                    

                     <div class="menushow" onclick="navigateTo('welcome.php')">
                            <i class="fa-brands fa-dashcube"></i>
                            <p>Dashboard</p>
                        </div>
                     

                     </div>
                        
                    </div>
                </div>
            </div>
            <?php if ($_SESSION['role'] == 'head' || $_SESSION['role'] == 'manager') : ?>
                <div class="menu">
                    <div class="menuitem" onclick="toggleMenu(this)">
                        <div class="menucontrol">
                            <div class="menushow">
                                <i class="fa-solid fa-file"></i>
                                <p>Visits</p>
                            </div>
                            <i class="fa-sharp fa-solid fa-caret-down"></i>
                        </div>
                        <ul>
                            <li onclick="navigateTo('bookaVisit.php')"><a href="#">Book a Visit</a></li>
                            <li onclick="navigateTo('visits.php')"><a href="#">Visitor Info</a></li>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>
            <?php if ($_SESSION['role'] == 'head' || $_SESSION['role'] == 'finance') : ?>
                <div class="menu">
                    <div class="menuitem" onclick="toggleMenu(this)">
                        <div class="menucontrol">
                            <div class="menushow">
                                <i class="fa-solid fa-user"></i>
                                <p>Memberships</p>
                            </div>
                            <i class="fa-sharp fa-solid fa-caret-down"></i>
                        </div>
                        <ul>
                            <li onclick="navigateTo('add_coworker.php')"><a href="#">Add a Coworker</a></li>
                            <li onclick="navigateTo('view_coworker.php')"><a href="#">List of coworker</a></li>
                            <li onclick="navigateTo('Team.php')"><a href="#">List of Team </a></li>
                        </ul>
                    </div>
                </div>
                <?php if ($_SESSION['role'] == 'head') : ?>
                <div class="menu">
                    <div class="menuitem" onclick="toggleMenu(this)">
                        <div class="menucontrol">
                            <div class="menushow">
                                <i class="fa-solid fa-ticket"></i>
                                <p>Tickets</p>
                            </div>
                            <i class="fa-sharp fa-solid fa-caret-down"></i>
                        </div>
                        <ul>
                            <li onclick="navigateTo('viewticket.php')"><a href="#">Info of All Tickets</a></li>
                            <li onclick="navigateTo('ticket.php')"><a href="#">Make a New Ticket</a></li>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>
            <?php endif; ?>
            <?php if ($_SESSION['role'] == 'head' || $_SESSION['role'] == 'finance') : ?>
                <div class="menu">
                    <div class="menuitem" onclick="toggleMenu(this)">
                        <div class="menucontrol">
                            <div class="menushow">
                                
                                <i class="fa-solid fa-envelope"></i>
                                <p>Contracts</p>
                            </div>
                            <i class="fa-sharp fa-solid fa-caret-down"></i>
                        </div>
                        <ul>
                            <li onclick="navigateTo('view_contracts.php')"><a href="#">View contracts</a></li>
                            
                        </ul>
                    </div>
                </div>
            <?php endif; ?>
            <?php if ($_SESSION['role'] == 'head') : ?>
                <div class="menu">
                    <div class="menuitem">
                        <div class="menucontrol" onclick="navigateTo('cal.html')">
                            <div class="menushow">
                                <i class="fa-solid fa-calendar-days"></i>
                                <p>Bookings</p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            <?php if ($_SESSION['role'] == 'head') : ?>
                <div class="menu">
                    <div class="menuitem">
                        <div class="menucontrol" onclick="navigateTo('seat.php')">
                            <div class="menushow">
                               
                                <i class="fa-solid fa-chair"></i> 
                                <p>Seats</p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            <?php if ($_SESSION['role'] == 'head' || $_SESSION['role'] == 'manager') : ?>
                <div class="menu">
                    <div class="menuitem" onclick="toggleMenu(this)">
                        <div class="menucontrol">
                            <div class="menushow">
                                <i class="fa-solid fa-sitemap"></i>
                                <p>Cowork Space Management</p>
                            </div>
                            <i class="fa-sharp fa-solid fa-caret-down"></i>
                        </div>
                        <ul>
                        <li onclick="navigateTo('office_bookings.php')"><a href="#">Office bookings</a></li>
                            <li onclick="navigateTo('office.php')"><a href="#">Office</a></li>
                            <li onclick="navigateTo('meetingRoom.php')"><a href="#">Meeting Room</a></li>
                            <li onclick="navigateTo('huddleRoom.php')"><a href="#">Huddle Room</a></li>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>
            <?php if ($_SESSION['role'] == 'head' || $_SESSION['role'] == 'finance') : ?>
                <div class="menu">
                    <div class="menuitem" onclick="toggleMenu(this)">
                        <div class="menucontrol">
                            <div class="menushow">
                                <i class="fa-sharp fa-solid fa-code-branch"></i>
                                <p>Branches</p>
                            </div>
                            <i class="fa-sharp fa-solid fa-caret-down"></i>
                        </div>
                        <ul>
                            <li onclick="navigateTo('branch.php')"><a href="#">Branches Data</a></li>
                            <li onclick="navigateTo('newBranch.php')"><a href="#">Add New Branch</a></li>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>
            <?php if ($_SESSION['role'] == 'head' || $_SESSION['role'] == 'manager') : ?>
                <div class="menu">
                    <div class="menuitem" onclick="toggleMenu(this)">
                        <div class="menucontrol">
                            <div class="menushow">
                            <i class="fa-sharp fa-solid fa-users"></i>
                                <p>Employee Management</p>
                            </div>
                            <i class="fa-sharp fa-solid fa-caret-down"></i>
                        </div>
                        <ul>
                            <li onclick="navigateTo('employeeData.php')"><a href="#">Employee Data</a></li>
                            <li onclick="navigateTo('view_leaves.php')"><a href="#">Leave of Employees</a></li>
                            
                        </ul>
                    </div>
                </div>
            <?php endif; ?>
           
            
           
            <?php if ($_SESSION['role'] == 'head' || $_SESSION['role'] == 'financehead') : ?>
                <div class="menu">
                    <div class="menuitem" onclick="toggleMenu(this)">
                        <div class="menucontrol">
                            <div class="menushow">
                                <i class="fa-solid fa-coins"></i>
                                <p>Finance</p>
                            </div>
                            <i class="fa-sharp fa-solid fa-caret-down"></i>
                        </div>
                        <ul>
                            <li onclick="navigateTo('pettyCash.php')"><a href="#">Petty Cash</a></li>
                            
                            <li onclick="navigateTo('financedisplay.php')"><a href="#">Rents</a></li>
                        </ul>
                    </div>
                   
                </div>
                
            <?php endif; ?>
            
            <div class="logout_btn_dashboard">
                        <a href="logout.php" class="logout-button">Logout</a>
                        </div>
            
           
        </div>

        <div class="content">
            <!-- Add content here or iframe if needed -->
            <iframe src="welcome.php" name="contentFrame"></iframe>
        </div>
    </div>

    <script>
        function toggleMenu(menu) {
            var menuItem = menu.closest('.menuitem');
            var subMenu = menuItem.querySelector('ul');
            if (subMenu) {
                menuItem.classList.toggle('active');
            }
        }

        function navigateTo(page) {
            if (page) {
                document.querySelector('iframe').src = page;
            }
        }
    </script>
    
</body>

</html>
