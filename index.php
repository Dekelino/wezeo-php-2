<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

date_default_timezone_set("Europe/Bratislava");

$timestamp = date("Y-m-d H:i:s");

$jsonFilePath = "allArrivals.json";

// Class for logging student arrivals
class ArrivalLogger
{
    private $jsonFileArrivalLogger;

    // Constructor to set the file path in ArrivalLogger class
    public function __construct($jsonFilePathConstruct)
    {
        $this->jsonFileArrivalLogger = $jsonFilePathConstruct;
    }

    // Check if there is a delay in arrival time
    private function isDelay($timestamp)
    {
        // Convert timestamp to Unix timestamp for comparison
        $timestampUnix = strtotime($timestamp); // strtotime - https://www.php.net/manual/en/function.strtotime.php

        // Check if arrival time is between 20:00 and 24:00
        $checkTimeFrom = strtotime('20:00:00');
        $checkTimeTo = strtotime('23:59:59'); //00:00:00 is the new day

        return ($timestampUnix >= $checkTimeFrom && $timestampUnix <= $checkTimeTo);
    }

    // Method to log arrival data
    public function logArrival($name, $timestamp)
    {
        // Check for delays and die if found
        if ($this->isDelay($timestamp)) {
            die("Arrival between 20:00 and 24:00 is not allowed.");
        }

        // Convert timestamp to Unix timestamp for comparison
        $timestampUnix = strtotime($timestamp);

        $checkTime = strtotime('08:00:00');

        // Set status based on check
        $status = ($timestampUnix > $checkTime) ? "meskanie" : "oka";

        // Create an array with arrival data
        $data = [
            "time" => $timestamp,
            "name" => $name,
            "status" => $status
        ];

        // Read existing arrival data from the JSON file
        $arrivalsData = file_get_contents($this->jsonFileArrivalLogger);
        $decodedJson = json_decode($arrivalsData, true) ?: []; //Null coalescing operator - handling wiht array[] if decode crash- decode-> https://code.tutsplus.com/how-to-parse-json-in-php--cms-36994t
        $decodedJson[] = $data;

        // Write the updated arrival data back to the JSON file
        file_put_contents($this->jsonFileArrivalLogger, json_encode($decodedJson, JSON_PRETTY_PRINT));
    }
}

// Class for logging student data - name+counter
class StudentLogger
{
    private $jsonFileStudentLogger;

    // Constructor to set the file path in class StudentLogger
    public function __construct($jsonFilePath)
    {
        $this->jsonFileStudentLogger = $jsonFilePath;
    }

    // Method to log student data
    public function logStudent($name)
    {
        // Read existing student data from the JSON file
        $studentsData = $this->readJsonFile($this->jsonFileStudentLogger);

        //If students and counters do not exist, create new arrays
        if (!isset($studentsData['students'])) {
            $studentsData['students'] = [];
        }
        if (!isset($studentsData['counters'])) {
            $studentsData['counters'] = [];
        }

        // Check if the student name already exists
        if (in_array($name, $studentsData['students'])) {
            // If it exists, increment the counter
            $studentsData['counters'][$name]++;
        } else {
            // Add the new name to the students.json array
            $studentsData['students'][] = $name;
            // Initialize the counter for the new student
            $studentsData['counters'][$name] = 1;
        }

        // Write the updated student data back to students.json
        $this->writeJsonFile($this->jsonFileStudentLogger, $studentsData);
    }

    private function readJsonFile($filename)
    {
        $jsonString = file_get_contents($filename);
        return json_decode($jsonString, true);
    }

    private function writeJsonFile($filename, $data)
    {
        $myJsonString = json_encode($data, JSON_PRETTY_PRINT);
        file_put_contents($filename, $myJsonString);
    }
}

// Function to display data froom allArrivals.json
function getDatas($jsonFilePath)
{
    $jsonData = file_get_contents($jsonFilePath);
    $output = json_decode($jsonData, true) ?: [];//Null coalescing operator again

    foreach ($output as $x) {
        echo $x['time'] . " " . $x['name'] . " " . $x['status'];
        echo '<br>';
    }
}

// Check if ?name="dummyName" parameter is in the URL,else from input
if (isset($_GET['name']) && ($_GET['name'] != "") || isset($_GET['addNameInpt'])) {
    // Get the student name from the URL, else from input
    $name = isset($_GET['name']) ? $_GET['name'] : $_GET['addNameInpt'];

    // Create ArrivalLogger
    $arrivalLogger = new ArrivalLogger($jsonFilePath);
    // Log the arrival for the student
    $arrivalLogger->logArrival($name, $timestamp);

    // Create StudentLogger
    $studentLogger = new StudentLogger('students.json');
    // Psush the student data
    $studentLogger->logStudent($name);

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

    <?php getDatas($jsonFilePath); ?>

</body>

</html>