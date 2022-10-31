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

// Initial config values for error reporting criteria
ini_set('error_reporting', -1);
//ini_set('display_errors', 1);
ini_set('html_errors', 1);

//===============================
// Global config variables
//===============================

$protocol = "https://";
$acronym = "HH";
$title = "Hendy's Hunches";
$version = "v2.1.3";
$year = "2022";
$last_update = "28th Oct 2022";
$base_url = $protocol."www.hendyshunches.co.uk";
$university_name = "The University of Nottingham";
$school_name = "School of Health Sciences";
$reports_dir = "/reports";
$backup_dir = "/bak";
$datalists_dir = "/text";
$sql_dir = "/sql";
$forum_dir = "/mboard";
$developer = "James Henderson";
$date_created = "9th Jun 2006";
$date_format = "d/m/Y (H:i)";

$prelim_groups = 8;
$prelim_fixtures = 48;
$prelim_teams = 32;
$knockout_fixtures = 16;

//===============================
// Game variables
//===============================

// Variable format groupXteamY
$A1 = "Qatar";
$A1img = "flag-icons/24/qatar.png";
$A2 = "Ecuador";
$A2img = "flag-icons/24/ecuador.png";
$A3 = "Senegal";
$A3img = "flag-icons/24/senegal.png";
$A4 = "Netherlands";
$A4img = "flag-icons/24/netherlands.png";

$B1 = "England";
$B1img = "flag-icons/24/england.png";
$B2 = "Iran";
$B2img = "flag-icons/24/iran.png";
$B3 = "USA";
$B3img = "flag-icons/24/usa.png";
$B4 = "Wales";
$B4img = "flag-icons/24/wales.png";

$C1 = "Argentina";
$C1img = "flag-icons/24/argentina.png";
$C2 = "Saudi Arabia";
$C2img = "flag-icons/24/saudi-arabia.png";
$C3 = "Mexico";
$C3img = "flag-icons/24/mexico.png";
$C4 = "Poland";
$C4img = "flag-icons/24/poland.png";

$D1 = "France";
$D1img = "flag-icons/24/france.png";
$D2 = "Australia";
$D2img = "flag-icons/24/australia.png";
$D3 = "Denmark";
$D3img = "flag-icons/24/denmark.png";
$D4 = "Tunisia";
$D4img = "flag-icons/24/tunisia.png";

$E1 = "Spain";
$E1img = "flag-icons/24/displayPersonalInfo.png";
$E2 = "Costa Rica";
$E2img = "flag-icons/24/costa-rica.png";
$E3 = "Germany";
$E3img = "flag-icons/24/germany.png";
$E4 = "Japan";
$E4img = "flag-icons/24/japan.png";

$F1 = "Belgium";
$F1img = "flag-icons/24/belgium.png";
$F2 = "Canada";
$F2img = "flag-icons/24/canada.png";
$F3 = "Morocco";
$F3img = "flag-icons/24/morocco.png";
$F4 = "Croatia";
$F4img = "flag-icons/24/croatia.png";

$G1 = "Brazil";
$G1img = "flag-icons/24/brazil.png";
$G2 = "Serbia";
$G2img = "flag-icons/24/serbia.png";
$G3 = "Switzerland";
$G3img = "flag-icons/24/switzerland.png";
$G4 = "Cameroon";
$G4img = "flag-icons/24/cameroon.png";

$H1 = "Portugal";
$H1img = "flag-icons/24/portugal.png";
$H2 = "Ghana";
$H2img = "flag-icons/24/ghana.png";
$H3 = "Uruguay";
$H3img = "flag-icons/24/uruguay.png";
$H4 = "South Korea";
$H4img = "flag-icons/24/korea-republic.png";

$R1 = "";
$R1img = "flag-icons/24/.png";
$R2 = "";
$R2img = "flag-icons/24/.png";
$R3 = "";
$R3img = "flag-icons/24/.png";
$R4 = "";
$R4img = "flag-icons/24/.png";
$R5 = "";
$R5img = "flag-icons/24/.png";
$R6 = "";
$R6img = "flag-icons/24/.png";
$R7 = "";
$R7img = "flag-icons/24/.png";
$R8 = "";
$R8img = "flag-icons/24/.png";
$R9 = "";
$R9img = "flag-icons/24/.png";
$R10 = "";
$R10img = "flag-icons/24/.png";
$R11 = "";
$R11img = "flag-icons/24/.png";
$R12 = "";
$R12img = "flag-icons/24/.png";
$R13 = "";
$R13img = "flag-icons/24/.png";
$R14 = "";
$R14img = "flag-icons/24/.png";
$R15 = "";
$R15img = "flag-icons/24/.png";
$R16 = "";
$R16img = "flag-icons/24/.png";

$Q1 = "";
$Q1img = "flag-icons/24/.png";
$Q2 = "";
$Q2img = "flag-icons/24/.png";
$Q3 = "";
$Q3img = "flag-icons/24/.png";
$Q4 = "";
$Q4img = "flag-icons/24/.png";
$Q5 = "";
$Q5img = "flag-icons/24/.png";
$Q6 = "";
$Q6img = "flag-icons/24/.png";
$Q7 = "";
$Q7img = "flag-icons/24/.png";
$Q8 = "";
$Q8img = "flag-icons/24/.png";

$S1 = "";
$S1img = "flag-icons/24/.png";
$S2 = "";
$S2img = "flag-icons/24/.png";
$S3 = "";
$S3img = "flag-icons/24/.png";
$S4 = "";
$S4img = "flag-icons/24/.png";

$P1 = "";
$P1img = "flag-icons/24/.png";
$P2 = "";
$P2img = "flag-icons/24/.png";

$Fi1 = "";
$Fi1img = "flag-icons/24/.png";
$Fi2 = "";
$Fi2img = "flag-icons/24/.png";

// Setup variables for kick-off times
// AST (UTC+3)

$_10 = "10:00 GMT";
$_13 = "13:00 GMT";
$_15 = "15:00 GMT";
$_16 = "16:00 GMT";
$_19 = "19:00 GMT";

// Setup variables for kick-off dates
$_20Nov = "20 November";
$_21Nov = "21 November";
$_22Nov = "22 November";
$_23Nov = "23 November";
$_24Nov = "24 November";
$_25Nov = "25 November";
$_26Nov = "26 November";
$_27Nov = "27 November";
$_28Nov = "28 November";
$_29Nov = "29 November";
$_30Nov = "30 November";
$_01Dec = "01 December";
$_02Dec = "02 December";
$_03Dec = "03 December";
$_04Dec = "04 December";
$_05Dec = "05 December";
$_06Dec = "06 December";
$_07Dec = "07 December";
$_08Dec = "08 December";
$_09Dec = "09 December";
$_10Dec = "10 December";
$_11Dec = "11 December";
$_12Dec = "12 December";
$_13Dec = "13 December";
$_14Dec = "14 December";
$_15Dec = "15 December";
$_16Dec = "16 December";
$_17Dec = "17 December";
$_18Dec = "18 December";

// Setup variables for venues
$venue1 = "Al Bayt Stadium";
$venue2 = "Khalifa International Stadium";
$venue3 = "Al Thumama Stadium";
$venue4 = "Ahmad Bin Ali Stadium";
$venue5 = "Kazan Arena, Kazan";
$venue6 = "Lusail Stadium";
$venue7 = "Stadium 974";
$venue8 = "Education City Stadium";
$venue9 = "Al Janoub Stadium";

// Setup variables for football kits
$fk1 = "football-kits/green-white.png";
$fk2 = "football-kits/blue-white.png";
$fk3 = "football-kits/red-white.png";
$fk4 = "football-kits/maroon-stripe.png";
$fk5 = "football-kits/sky-blue-stripes.png";
$fk6 = "football-kits/yellow-green.png";
$fk7 = "football-kits/pink-white.png";
$fk8 = "football-kits/purple-grad.png";
$fk9 = "football-kits/orange-black.png";
$fk10 = "football-kits/mint-grey-square.png";
$fk11 = "football-kits/black-white.png";
$fk12 = "football-kits/diag-navy-red.png";
$fk13 = "football-kits/claret-blue-square.png";
$fk14 = "football-kits/blue-yellow.png";
$fk15 = "football-kits/blue-white-hoops.png";
$fk16 = "football-kits/green-white-hoops.png";
$fk17 = "football-kits/red-grad.png";
$fk18 = "football-kits/pink-grad.png";

//===============================
// Global 'helper' functions
//===============================

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
