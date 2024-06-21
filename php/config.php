<?php

/***********************************
* Application: Hendy's Hunches
* File: config.inc.php
* Created By: James Henderson
* Date: 25/05/2021
***********************************/

//===============================
// Testing and reporting
//===============================

// Initial error reporting criteria
// ini_set('error_reporting', -1);
// ini_set('display_errors', 1);
// ini_set('html_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

//===============================
// Global variables
//===============================

$acronym = "HH";
$title = "Hendy's Hunches";
$version = "v2.9.1";
$year = "2024";
$last_update = "15/06/2024";
$base_url = "https://www.hendyshunches.co.uk";
$forgot_pwd_url = "https://www.hendyshunches.co.uk/forgot-password.php";
$backup_dir = "/bak";
$datalists_dir = "/text";
$sql_dir = "/sql";
$forum_dir = "/mboard";
$developer = "James Henderson";
$date_created = "09/06/2006";
$date_format = "d/m/Y (H:i)";
$competition = "UEFA EURO 2024™";
$competition_url = "https://www.uefa.com/euro2024/";
$competition_location = "Germany";
$competition_favourites = "England";
$charity = "Notts County Foundation";
$charity_url = "https://www.nottscountyfoundation.org.uk/programme/on-the-ball/";
$signup_fee = 5; // No of GBP £
$charity_fee = 2; // No of GBP £
$prize_fee = 2; // No of GBP £
$signup_fee_formatted = sprintf("%01.2f", $signup_fee);
$charity_fee_formatted = sprintf("%01.2f", $charity_fee);
$prize_fee_formatted = sprintf("%01.2f", $prize_fee);
$signup_close_date = "13/06/2024";
$signup_url = "https://monzo.me/jamescolinhenderson/5.00?d=Hendy%27s%20Hunches%20-%20%5BYour%20Name%5D";


//===============================
// Game variables
//===============================

$no_of_competition_groups = 6;
$no_of_competition_teams = 24;
$no_of_group_fixtures = 36;
$no_of_knockout_fixtures = 15;
$no_of_ro16_fixtures = 8;
$no_of_qf_fixtures = 4;
$no_of_sf_fixtures = 2;
$no_of_final_fixtures = 1;
$no_of_total_fixtures = 51; // p and r values to *2 of this value
$competition_start_date = "14/06/2024";
$competition_end_date = "14/07/2024";
$group_fixtures_start_date = "14/06/2024";
$group_fixtures_end_date = "26/06/2024";
$knockout_fixtures_start_date = "29/06/2024";
$round_of_16_start_date = "29/06/2024";
$round_of_16_end_date = "02/07/2024";
$quarter_final_start_date = "05/07/2024";
$quarter_final_end_date = "06/07/2024";
$semi_final_start_date = "09/07/2024";
$semi_final_end_date = "10/07/2024";
$final_date = "14/07/2024";

// Variable format groupXteamY
$A1 = "Germany";
$A1_img = "flag-icons/24/germany.png";
$A2 = "Scotland";
$A2_img = "flag-icons/24/scotland.png";
$A3 = "Hungary";
$A3_img = "flag-icons/24/hungary.png";
$A4 = "Switzerland";
$A4_img = "flag-icons/24/switzerland.png";

$B1 = "Spain";
$B1_img = "flag-icons/24/spain.png";
$B2 = "Croatia";
$B2_img = "flag-icons/24/croatia.png";
$B3 = "Italy";
$B3_img = "flag-icons/24/italy.png";
$B4 = "Albania";
$B4_img = "flag-icons/24/albania.png";

$C1 = "Slovenia";
$C1_img = "flag-icons/24/slovenia.png";
$C2 = "Denmark";
$C2_img = "flag-icons/24/denmark.png";
$C3 = "Serbia";
$C3_img = "flag-icons/24/serbia.png";
$C4 = "England";
$C4_img = "flag-icons/24/england.png";

$D1 = "Poland";
$D1_img = "flag-icons/24/poland.png";
$D2 = "Netherlands";
$D2_img = "flag-icons/24/netherlands.png";
$D3 = "Austria";
$D3_img = "flag-icons/24/austria.png";
$D4 = "France";
$D4_img = "flag-icons/24/france.png";

$E1 = "Belgium";
$E1_img = "flag-icons/24/belgium.png";
$E2 = "Slovakia";
$E2_img = "flag-icons/24/slovakia.png";
$E3 = "Romania";
$E3_img = "flag-icons/24/romania.png";
$E4 = "Ukraine";
$E4_img = "flag-icons/24/ukraine.png";

$F1 = "Türkiye";
$F1_img = "flag-icons/24/türkiye.png";
$F2 = "Georgia";
$F2_img = "flag-icons/24/georgia.png";
$F3 = "Portugal";
$F3_img = "flag-icons/24/portugal.png";
$F4 = "Czechia";
$F4_img = "flag-icons/24/czech-republic.png";

// Setup variables for kick-off times
// CET (UTC+1)
$_10 = "10:00";
$_13 = "13:00";
$_14 = "14:00";
$_15 = "15:00";
$_16 = "16:00";
$_17 = "17:00";
$_19 = "19:00";
$_20 = "20:00";

// Setup variables for kick-off dates
$_14Jun = "14 JUN";
$_15Jun = "15 JUN";
$_16Jun = "16 JUN";
$_17Jun = "17 JUN";
$_18Jun = "18 JUN";
$_19Jun = "19 JUN";
$_20Jun = "20 JUN";
$_21Jun = "21 JUN";
$_22Jun = "22 JUN";
$_23Jun = "23 JUN";
$_24Jun = "24 JUN";
$_25Jun = "25 JUN";
$_26Jun = "26 JUN";

// Setup variables for venues
$venue1 = "Munich";
$venue2 = "Cologne";
$venue3 = "Berlin";
$venue4 = "Dortmund";
$venue5 = "Hamburg";
$venue6 = "Stuttgart";
$venue7 = "Gelsenkirchen";
$venue8 = "Frankfurt";
$venue9 = "Dusseldorf";
$venue10 = "Leipzig";


$R1 = "";
$R1_img = "flag-icons/24/.png";
$R2 = "";
$R2_img = "flag-icons/24/.png";
$R3 = "";
$R3_img = "flag-icons/24/.png";
$R4 = "";
$R4_img = "flag-icons/24/.png";
$R5 = "";
$R5_img = "flag-icons/24/frace.png";
$R6 = "";
$R6_img = "flag-icons/24/.png";
$R7 = "";
$R7_img = "flag-icons/24/.png";
$R8 = "";
$R8_img = "flag-icons/24/.png";
$R9 = "";
$R9_img = "flag-icons/24/.png";
$R10 = "";
$R10_img = "flag-icons/24/.png";
$R11 = "";
$R11_img = "flag-icons/24/.png";
$R12 = "";
$R12_img = "flag-icons/24/.png";
$R13 = "";
$R13_img = "flag-icons/24/.png";
$R14 = "";
$R14_img = "flag-icons/24/.png";
$R15 = "";
$R15_img = "flag-icons/24/.png";
$R16 = "";
$R16_img = "flag-icons/24/.png";

$Q1 = "";
$Q1_img = "flag-icons/24/.png";
$Q2 = "";
$Q2_img = "flag-icons/24/.png";
$Q3 = "";
$Q3_img = "flag-icons/24/.png";
$Q4 = "";
$Q4_img = "flag-icons/24/.png";
$Q5 = "";
$Q5_img = "flag-icons/24/.png";
$Q6 = "";
$Q6_img = "flag-icons/24/.png";
$Q7 = "";
$Q7_img = "flag-icons/24/.png";
$Q8 = "";
$Q8_img = "flag-icons/24/.png";

$S1 = "";
$S1_img = "flag-icons/24/.png";
$S2 = "";
$S2_img = "flag-icons/24/.png";
$S3 = "";
$S3_img = "flag-icons/24/.png";
$S4 = "";
$S4_img = "flag-icons/24/.png";

$P1 = "";
$P1_img = "flag-icons/24/.png";
$P2 = "";
$P2_img = "flag-icons/24/.png";

$Fi1 = "";
$Fi1_img = "flag-icons/24/.png";
$Fi2 = "";
$Fi2_img = "flag-icons/24/.png";

// Setup variables for football kits
$fk1 = "football-kits/green-white.png";
$fk2 = "football-kits/blue-white.png";
$fk3 = "football-kits/red-white-blue.png";
$fk4 = "football-kits/maroon-white.png";
$fk5 = "football-kits/skyblue-blue-hoops.png";
$fk6 = "football-kits/yellow-black.png";
$fk7 = "football-kits/pink-black.png";
$fk8 = "football-kits/purple-black.png";
$fk9 = "football-kits/orange-purple.png";
$fk10 = "football-kits/grey-mint.png";
$fk11 = "football-kits/charcoal-gold.png";
$fk12 = "football-kits/green-lightgreen.png";
$fk13 = "football-kits/claret-lightblue.png";
$fk14 = "football-kits/lightred-lightyellow.png";
$fk15 = "football-kits/navy-blue-red.png";
$fk16 = "football-kits/white-green.png";
$fk17 = "football-kits/red-black.png";
$fk18 = "football-kits/pink-white.png";

//===============================
// Global 'helper' functions
//===============================

function returnAvatar() {
	// Create DB connection
	include 'db-connect.php';

	// Get team information from the DB	counting occurrences too
	$sql_getavatar = "SELECT firstname, surname, avatar FROM live_user_information WHERE username = '".$_SESSION["username"]."'";
	$getavatar = mysqli_query($con, $sql_getavatar);
	$userid = mysqli_fetch_assoc($getavatar);
	$firstname = $userid["firstname"];
	$surname = $userid["surname"];
	$avatar = $userid["avatar"];
	print("<img src='$avatar' id='avatar' class='img-fluid rounded-circle mx-1' alt='User Avatar' name='User Avatar' width='25'> $firstname " . "$surname");
}

function formatDateAsSystem($date) {
	echo date_format($date, $date_format);
}

function formatDateAsUK($date) {
	echo date_format($date, "dd/mm/YY");
}

// Display an alert style message
function alertMsg($msg) {
	echo "<script type='text/javascript'>alert('Alert message: " . $msg . "');</script>";
}
// Write a console debug message
function consoleMsg($data) {
    $output = $data;
    if (is_array($output))
        $output = implode(',', $output);
    echo "<script>console.log('Debug message: " . $output . "');</script>";
}
?>
