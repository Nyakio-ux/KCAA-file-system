<?php
// File: includes/db.php
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'kcaa';

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

<!-- File: add-file.php -->
<?php
include 'includes/db.php';
session_start();
if (!isset($_SESSION['fmsuid'])) {
    header("Location: home.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $desc = $_POST['file_description'];
    $type = $_POST['file_type'];
    $originator = $_SESSION['fmsuid'];
    $receiver = $_POST['receiver_id'];
    $orig_office = $_POST['origination_office'];
    $recv_office = $_POST['receiving_office'];
    $date_orig = $_POST['date_of_origination'];

    $sql = "INSERT INTO file_movements (file_description, file_type, originator_id, receiver_id, origination_office_id, receiving_office_id, date_of_origination)
            VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssiiiis", $name, $type, $originator, $receiver, $orig_office, $recv_office, $date_orig);
    if ($stmt->execute()) {
        echo "<p>File added successfully!</p>";
    } else {
        echo "<p>Error: " . $stmt->error . "</p>";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add File Movement</title>
</head>
<body>
    <h2>Add New File Movement</h2>
    <form method="POST">
        <label>Name</label>
        <input type="text" name="file_name" required><br>


        <label>Receiver:</label>
        <input type="number" name="receiver_id" required><br>

        <label>Originating Office:</label>
        <input type="number" name="origination_office" required><br>

        <label>Receiving Office:</label>
        <input type="number" name="receiving_office" required><br>

        <label>Date of Origination:</label>
        <input type="date" name="date_of_origination" required><br>

        <button type="submit">Submit</button>
    </form>
</body>
</html>


