<?php
// Database connection
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'kcaa'; // Replace with your admin database name

$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("<p class='error'>Database connection failed: " . $conn->connect_error . "</p>");
}

// Fetch departments from the database
$departments = [];
$result = $conn->query("SELECT name FROM departments"); // Replace 'departments' with your table name
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $departments[] = $row['name'];
    }
}

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uploadDir = 'uploads/'; // Directory to save uploaded files
    $uploadFile = isset($_FILES['file']) ? $uploadDir . basename($_FILES['file']['name']) : null;
    $maxFileSize = 2 * 1024 * 1024; // Maximum file size (2 MB)
    $allowedTypes = ['image/jpeg', 'image/png', 'application/pdf']; // Allowed MIME types

    // Create the upload directory if it doesn't exist
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Validate file upload if a file is provided
    if (isset($_FILES['file']) && $_FILES['file']['size'] > 0) {
        if ($_FILES['file']['size'] > $maxFileSize) {
            echo "<p class='error'>Error: File size exceeds the maximum limit of 2 MB.</p>";
            exit;
        }

        if (!in_array($_FILES['file']['type'], $allowedTypes)) {
            echo "<p class='error'>Error: Invalid file type. Only JPEG, PNG, and PDF files are allowed.</p>";
            exit;
        }

        if (!move_uploaded_file($_FILES['file']['tmp_name'], $uploadFile)) {
            echo "<p class='error'>Error: Failed to upload file.</p>";
            exit;
        }
    }

    // Collect metadata from the form
    $fileName = htmlspecialchars($_POST['physical_file_name'] ?? ($_FILES['file']['name'] ?? ''));
    $department = htmlspecialchars($_POST['department']);
    $originator = htmlspecialchars($_POST['originator']);
    $destination = htmlspecialchars($_POST['destination']);
    $receiver = htmlspecialchars($_POST['receiver']);
    $dateTime = date('Y-m-d H:i:s'); // Current date and time

    // Save metadata to the database
    $stmt = $conn->prepare("
        INSERT INTO uploaded_files (file_name, department, originator, destination, receiver, uploaded_at) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("ssssss", $fileName, $department, $originator, $destination, $receiver, $dateTime);

    if ($stmt->execute()) {
        echo "<p class='success'>File uploaded successfully!</p>";
    } else {
        echo "<p class='error'>Error saving file metadata: " . $stmt->error . "</p>";
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload File</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 20px;
        }

        h1 {
            text-align: center;
            color: #333;
        }

        form {
            max-width: 500px;
            margin: 0 auto;
            padding: 20px;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #555;
        }

        input[type="text"],
        input[type="file"],
        input[type="date"],
        select {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        input[type="date"] {
            background-color: #f9f9f9;
            color: #333;
            font-size: 14px;
        }

        input[type="date"]:focus {
            border-color: #007BFF;
            outline: none;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
        }

        button {
            display: block;
            width: 100%;
            padding: 10px;
            background-color: #007BFF;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <h1>Upload File</h1>
    <form action="upload.php" method="post" enctype="multipart/form-data">
        <label for="file">Choose a file (optional):</label>
        <input type="file" name="file" id="file"><br><br>

        <label for="physical_file_name">File Name (for physical file movement):</label>
        <input type="text" name="physical_file_name" id="physical_file_name"><br><br>

        <label for="department">Origin Office:</label>
        <select name="department" id="department" required>
            <option value="">Select a department</option>
            <?php foreach ($departments as $department): ?>
                <option value="<?php echo htmlspecialchars($department); ?>"><?php echo htmlspecialchars($department); ?></option>
            <?php endforeach; ?>
        </select><br><br>

        <label for="originator">Originator:</label>
        <input type="text" name="originator" id="originator" required><br><br>

        <label for="destination">Destination Office:</label>
        <select name="destination" id="destination" required>
            <option value="">Select a destination</option>
            <?php foreach ($departments as $department): ?>
                <option value="<?php echo htmlspecialchars($department); ?>"><?php echo htmlspecialchars($department); ?></option>
            <?php endforeach; ?>
        </select><br><br>

        <label for="receiver">Receiver:</label>
        <input type="text" name="receiver" id="receiver" required><br><br>

        <label for="date_of_origination">Date of Origination:</label>
        <input type="date" name="date_of_origination" id="date_of_origination" required><br><br>
        
        <button type="submit">Upload</button>
    </form>
</body>
</html>