<?php
session_start();

function hh_login_table_has_column(mysqli $connection, string $tableName, string $columnName): bool
{
    $safeTable = mysqli_real_escape_string($connection, $tableName);
    $safeColumn = mysqli_real_escape_string($connection, $columnName);
    $result = mysqli_query($connection, "SHOW COLUMNS FROM {$safeTable} LIKE '{$safeColumn}'");
    if (!($result instanceof mysqli_result)) {
        return false;
    }

    $hasColumn = mysqli_num_rows($result) > 0;
    mysqli_free_result($result);

    return $hasColumn;
}

function hh_login_table_exists(mysqli $connection, string $tableName): bool
{
    $safeTable = mysqli_real_escape_string($connection, $tableName);
    $result = mysqli_query($connection, "SHOW TABLES LIKE '{$safeTable}'");
    if (!($result instanceof mysqli_result)) {
        return false;
    }

    $exists = mysqli_num_rows($result) > 0;
    mysqli_free_result($result);

    return $exists;
}

// Sanitize incoming username and password
$username = $_POST['username'];
$password = $_POST['password'];

include 'db-connect.php';

$stmt = mysqli_prepare($con, "SELECT id FROM live_user_information WHERE username = ? and password = md5(?)");

// Bind the input parameters to the prepared statement
mysqli_stmt_bind_param($stmt, "ss", $username, $password);

// Execute the query
mysqli_stmt_execute($stmt);

// Store the result so we can determine how many rows have been returned
mysqli_stmt_store_result($stmt);

if (mysqli_stmt_num_rows($stmt) == 1) {

    // Bind the returned user ID to the $id variable
    mysqli_stmt_bind_result($stmt, $id);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    $hasLoginCountColumn = hh_login_table_has_column($con, 'live_user_information', 'login_count');
    $hasLoginEventsTable = hh_login_table_exists($con, 'live_user_logins');

    // Update the account's login tracking
    if ($hasLoginCountColumn) {
        $stmt = mysqli_prepare($con, "UPDATE live_user_information SET lastlogin = NOW(), login_count = login_count + 1 WHERE id = ?");
    } else {
        $stmt = mysqli_prepare($con, "UPDATE live_user_information SET lastlogin = NOW() WHERE id = ?");
    }

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    if ($hasLoginEventsTable) {
        $stmt = mysqli_prepare($con, "INSERT INTO live_user_logins (user_id) VALUES (?)");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }

    // Retrieve the corresponding login information into session variables
    $stmt = mysqli_prepare($con, "SELECT id, username, password, firstname, surname FROM live_user_information WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $id, $username, $password, $firstname, $surname);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    // Assign user session variables
    $_SESSION['id'] = $id;
    $_SESSION['username'] = $username;
    $_SESSION['password'] = $password;
    $_SESSION['firstname'] = $firstname;
    $_SESSION['surname'] = $surname;
    $_SESSION['login'] = "1";

    // Redirect the user to the successful page
    header('Location: ../dashboard.php');
    exit;
}

$_SESSION['login'] = "";

// Redirect the user to the 'unsuccessful' page
header('Location: ../index.php');
exit;
