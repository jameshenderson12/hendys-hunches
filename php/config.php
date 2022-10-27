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
$version = "v2.1.2";
$year = "2022";
$last_update = "26th Oct 2022";
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
$prelim_fixtures = XX;
$prelim_teams = 32;
$knockout_fixtures = 16;

//===============================
// Game variables
//===============================

// Variable format groupXteamY
$A1 = "Qatar";
$A1img = "flag-icons/24/qatar.png";
$A2 = "Saudi Arabia";
$A2img = "flag-icons/24/saudi-arabia.png";
$A3 = "Egypt";
$A3img = "flag-icons/24/egypt.png";
$A4 = "Uruguay";
$A4img = "flag-icons/24/uruguay.png";

$B1 = "Portugal";
$B1img = "flag-icons/24/portugal.png";
$B2 = "Spain";
$B2img = "flag-icons/24/spain.png";
$B3 = "Morocco";
$B3img = "flag-icons/24/morocco.png";
$B4 = "Iran";
$B4img = "flag-icons/24/iran.png";

$C1 = "France";
$C1img = "flag-icons/24/france.png";
$C2 = "Australia";
$C2img = "flag-icons/24/australia.png";
$C3 = "Peru";
$C3img = "flag-icons/24/peru.png";
$C4 = "Denmark";
$C4img = "flag-icons/24/denmark.png";

$D1 = "Argentina";
$D1img = "flag-icons/24/argentina.png";
$D2 = "Iceland";
$D2img = "flag-icons/24/iceland.png";
$D3 = "Croatia";
$D3img = "flag-icons/24/croatia.png";
$D4 = "Nigeria";
$D4img = "flag-icons/24/nigeria.png";

$E1 = "Brazil";
$E1img = "flag-icons/24/brazil.png";
$E2 = "Switzerland";
$E2img = "flag-icons/24/switzerland.png";
$E3 = "Costa Rica";
$E3img = "flag-icons/24/costa-rica.png";
$E4 = "Serbia";
$E4img = "flag-icons/24/serbia.png";

$F1 = "Germany";
$F1img = "flag-icons/24/germany.png";
$F2 = "Mexico";
$F2img = "flag-icons/24/mexico.png";
$F3 = "Sweden";
$F3img = "flag-icons/24/sweden.png";
$F4 = "Korea Republic";
$F4img = "flag-icons/24/korea.png";

$G1 = "Belgium";
$G1img = "flag-icons/24/belgium.png";
$G2 = "Panama";
$G2img = "flag-icons/24/panama.png";
$G3 = "Tunisia";
$G3img = "flag-icons/24/tunisia.png";
$G4 = "England";
$G4img = "flag-icons/24/england.png";

$H1 = "Poland";
$H1img = "flag-icons/24/poland.png";
$H2 = "Senegal";
$H2img = "flag-icons/24/senegal.png";
$H3 = "Colombia";
$H3img = "flag-icons/24/colombia.png";
$H4 = "Japan";
$H4img = "flag-icons/24/japan.png";

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
$_12pm = "12:00";
$_1pm = "13:00";
$_2pm = "14:00";
$_3pm = "15:00";
$_4pm = "16:00";
$_5pm = "17:00";
$_6pm = "18:00";
$_7pm = "19:00";
$_8pm = "20:00";
$_9pm = "21:00";
$_10pm = "22:00";
$_11pm = "23:00";
$_12am = "24:00";
$_1am = "01:00";
$_2am = "02:00";
$_3am = "03:00";
$_4am = "04:00";
$_5am = "05:00";
$_6am = "06:00";
$_7am = "07:00";
$_8am = "08:00";
$_9am = "09:00";
$_10am = "10:00";
$_11am = "11:00";

// Setup variables for kick-off dates
$_10Jun = "10 June";
$_11Jun = "11 June";
$_12Jun = "12 June";
$_13Jun = "13 June";
$_14Jun = "14 June";
$_15Jun = "15 June";
$_16Jun = "16 June";
$_17Jun = "17 June";
$_18Jun = "18 June";
$_19Jun = "19 June";
$_20Jun = "20 June";
$_21Jun = "21 June";
$_22Jun = "22 June";
$_23Jun = "23 June";
$_24Jun = "24 June";
$_25Jun = "25 June";
$_26Jun = "26 June";
$_27Jun = "27 June";
$_28Jun = "28 June";
$_29Jun = "29 June";
$_30Jun = "30 June";
$_01Jul = "01 July";
$_02Jul = "02 July";
$_03Jul = "03 July";
$_04Jul = "04 July";
$_05Jul = "05 July";
$_06Jul = "06 July";
$_07Jul = "07 July";
$_08Jul = "08 July";
$_09Jul = "09 July";
$_10Jul = "10 July";
$_11Jul = "11 July";
$_12Jul = "12 July";
$_13Jul = "13 July";
$_14Jul = "14 July";
$_15Jul = "15 July";
$_16Jul = "16 July";
$_17Jul = "17 July";
$_18Jul = "18 July";
$_19Jul = "19 July";
$_20Jul = "20 July";

// Setup variables for venues
$venue1 = "Luzhniki Stadium, Moscow";
$venue2 = "Ekaterinburg Arena, Ekaterinburg";
$venue3 = "Saint Petersburg Stadium, St. Petersburg";
$venue4 = "Fisht Stadium, Sochi";
$venue5 = "Kazan Arena, Kazan";
$venue6 = "Spartak Stadium, Moscow";
$venue7 = "Kaliningrad Stadium, Kaliningrad";
$venue8 = "Samara Arena, Samara";
$venue9 = "Rostov Arena, Rostov-On-Don";
$venue10 = "Nizhny Novgorod Stadium, Nizhny Novgorod";
$venue11 = "Volgograd Arena, Volgograd";
$venue12 = "Mordovia Arena, Saransk";

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
