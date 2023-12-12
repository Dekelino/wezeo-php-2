<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

date_default_timezone_set("Europe/Bratislava");
$timestamp = date("Y-m-d H:i:s");

$jsonFilePath = "allArrivals.json";

function checkArrival($file, $name)
{
    global $timestamp;

    $timestampUnix = strtotime($timestamp); // strtotime - https://www.php.net/manual/en/function.strtotime.php

    // Check if arrival time is between 20:00 and 24:00
    $checkTimeFrom = strtotime('20:00:00');
    $checkTimeTo = strtotime('23:59:59'); //00:00:00 is the new day

    if ($timestampUnix >= $checkTimeFrom && $timestampUnix <= $checkTimeTo) {
        die("Arrival between 20:00 and 24:00 is not allowed.");
    }

    $checkTime = strtotime('08:00:00');

    $status = ($timestampUnix > $checkTime) ? "meskanie" : "ok";
    $data = [
        "time" => $timestamp,
        "name" => $name,
        "status" => $status
    ];

    $arrivalsData = file_get_contents($file);
    $arrivals = json_decode($arrivalsData, true) ?: [];
    $arrivals[] = $data;

    // Write the updated arrival data back to the JSON file
    file_put_contents($file, json_encode($arrivals, JSON_PRETTY_PRINT));

    // Update students.json
    $studentsFile = 'students.json';
    $studentsData = readJsonFile($studentsFile);

    // If students and counters do not exist, create new arrays
    if (!isset($studentsData['students'])) {
        $studentsData['students'] = [];
    }
    if (!isset($studentsData['counters'])) {
        $studentsData['counters'] = [];
    }

    if (in_array($name, $studentsData['students'])) {
        // If it exists, increment the counter
        $studentsData['counters'][$name]++;
    } else {
        // Add the new name to the students array
        $studentsData['students'][] = $name;
        $studentsData['counters'][$name] = 1;
    }

    // Write the updated data back to students.json
    file_put_contents($studentsFile, json_encode($studentsData, JSON_PRETTY_PRINT));
}

function getDatas()
{
    global $jsonFilePath;

    $jsonData = file_get_contents($jsonFilePath);
    $output = json_decode($jsonData, true) ?: [];

    foreach ($output as $x) {
        echo $x['time'] . " " . $x['name'] . " " . $x['status'];
        echo '<br>';
    }
}

// Function to read the JSON file and return its contents as an array
function readJsonFile($filename)
{
    $jsonString = file_get_contents($filename);
    return json_decode($jsonString, true);
}

function writeJsonFile($filename, $data)
{
    $myJsonString = json_encode($data, JSON_PRETTY_PRINT);
    file_put_contents($filename, $myJsonString);
}

// Check if the "name" parameter is present in the URL
if (isset($_GET['name']) && ($_GET['name'] != "") || isset($_GET['addNameInpt'])) {
    $name = isset($_GET['name']) ? $_GET['name'] : $_GET['addNameInpt'];

    checkArrival($jsonFilePath, $name);

    echo "Name '$name' added to students.";
} else {
    echo "Please write your name using '?name=' in your URL ";
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wezeo PHP academy </title>
</head>

<body>
    <h1>Arrivals</h1>
    <form method="get" action="">
        <input type="submit" name="addNameBtn" value="Submit">
        <input type="text" name="addNameInpt" placeholder="Student name" required>
    </form>
    <h2> Last timestamp : <?php echo $timestamp; ?></h2>
    <p>Log data:</p>

    <?php getDatas() ?>

</body>

</html>