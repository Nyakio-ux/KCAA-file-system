<?php
// Database connection
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'kcaa';

$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("<p class='error'>Database connection failed: " . $conn->connect_error . "</p>");
}

// Fetch departments from the database
$departments = [];
$result = $conn->query("SELECT department_name FROM departments");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $departments[] = $row['department_name'];
    }
}

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uploadDir = 'uploads/';
    $uploadFile = isset($_FILES['file']) ? $uploadDir . basename($_FILES['file']['name']) : null;

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    if (!isset($_FILES['file']) || $_FILES['file']['size'] === 0) {
        echo "<p class='error'>Error: No file uploaded.</p>";
        die();
    }

    if (!move_uploaded_file($_FILES['file']['tmp_name'], $uploadFile)) {
        echo "<p class='error'>Error: Failed to upload file.</p>";
        die();
    }

    $fileName = htmlspecialchars($_POST['file_name'] ?? ($_FILES['file']['name'] ?? ''));
    $referenceNo = htmlspecialchars($_POST['reference_no']);
    $filePath = $uploadFile;
    $department = htmlspecialchars($_POST['department']);
    $originator = htmlspecialchars($_POST['originator']);
    $destination = htmlspecialchars($_POST['destination']);
    $receiver = htmlspecialchars($_POST['receiver']);
    $dateOfOrigination = htmlspecialchars($_POST['date_of_origination']);
    $comments = htmlspecialchars($_POST['comments']);
    $dateTime = date('Y-m-d H:i:s');

    $stmt = $conn->prepare("
        INSERT INTO files (file_name, reference_no, file_path, department, originator, destination, receiver, date_of_origination, comments, uploaded_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("ssssssssss", $fileName, $referenceNo, $filePath, $department, $originator, $destination, $receiver, $dateOfOrigination, $comments, $dateTime);

    if ($stmt->execute()) {
        echo "<p class='success'>File uploaded successfully.</p>";
    } else {
        echo "<p class='error'>Error saving file metadata: " . $stmt->error . "</p>";
    }

    $stmt->close();
    $conn->close();
    die();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>File Upload</title>
    <style>
    body {
        font-family: Arial, sans-serif;
        background-color: #1a202c;
        margin: 0;
        padding: 0;
    }

    .main-content {
        padding: 40px;
        color: #fff;
        min-height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    form#uploadForm {
        background-color: #2d3748;
        padding: 30px;
        border-radius: 10px;
        width: 100%;
        max-width: 600px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.4);
    }

    form#uploadForm label {
        display: block;
        margin-top: 15px;
        color: #cbd5e0;
        font-weight: bold;
    }

    form#uploadForm input,
    form#uploadForm select {
        width: 100%;
        padding: 10px;
        margin-top: 5px;
        border: 1px solid #4a5568;
        border-radius: 5px;
        background-color: #1a202c;
        color: #fff;
    }

    form#uploadForm button {
        margin-top: 20px;
        padding: 12px;
        background-color: #3182ce;
        color: #fff;
        border: none;
        border-radius: 5px;
        font-size: 16px;
        cursor: pointer;
    }

    form#uploadForm button:hover {
        background-color: #2b6cb0;
    }

    #message {
        margin-top: 20px;
        text-align: center;
        font-size: 16px;
    }

    #message.success {
        color: #38a169;
    }

    #message.error {
        color: #e53e3e;
    }
</style>
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>
<body>
    

    <div class="main-content" id="mainContent">
        <form id="uploadForm" method="post" enctype="multipart/form-data">
            <h2 style="color: #fff; text-align: center; margin-bottom: 20px;">Upload File</h2>
            
            <!-- File Name -->
            <label for="file_name">File Name:</label>
            <input type="text" name="file_name" id="file_name" placeholder="Enter file name" required>
            
            <!-- Reference Number -->
            <label for="reference_no">Reference Number:</label>
            <input type="text" name="reference_no" id="reference_no" placeholder="Enter reference number" required>
            
            <!-- Origin Office -->
            <label for="department">Origin Office:</label>
            <select name="department" id="department" required>
                <option value="">Select a department</option>
                <?php foreach ($departments as $department): ?>
                    <option value="<?php echo htmlspecialchars($department); ?>"><?php echo htmlspecialchars($department); ?></option>
                <?php endforeach; ?>
            </select>
            
            <!-- Originator -->
            <label for="originator">Originator:</label>
            <input type="text" name="originator" id="originator" placeholder="Enter originator name" required>
            
            <!-- Destination Office -->
            <label for="destination">Destination Office:</label>
            <select name="destination" id="destination" required>
                <option value="">Select a destination</option>
                <?php foreach ($departments as $department): ?>
                    <option value="<?php echo htmlspecialchars($department); ?>"><?php echo htmlspecialchars($department); ?></option>
                <?php endforeach; ?>
            </select>
            
            <!-- Receiver -->
            <label for="receiver">Receiver:</label>
            <input type="text" name="receiver" id="receiver" placeholder="Enter receiver name" required>
            
            <!-- Comments -->
            <label for="comments">Comments:</label>
            <select name="comments" id="comments" required>
                <option value="-">-</option>
                <option value="Sent">Sent</option>
                <option value="Received">Received</option>
                <option value="Approved">Approved</option>
            </select>
            
            <!-- Date of Origination -->
            <label for="date_of_origination">Date of Origination:</label>
            <input type="date" name="date_of_origination" id="date_of_origination" required>
            
            
            <!-- Submit Button -->
            <button type="submit">Upload</button>
        </form>
        <div id="message"></div>
    </div>

    <script>
        function loadContent(page) {
            fetch(page)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('mainContent').innerHTML = html;
                })
                .catch(error => {
                    document.getElementById('mainContent').innerHTML = "<p>Error loading content.</p>";
                });
        }

        document.getElementById("uploadForm")?.addEventListener("submit", function (event) {
            event.preventDefault();
            const formData = new FormData(this);
            fetch("", { method: "POST", body: formData })
                .then(response => response.text())
                .then(data => {
                    document.getElementById("message").innerHTML = data;
                    this.reset();
                })
                .catch(error => {
                    document.getElementById("message").innerHTML = "<p class='error'>An error occurred. Please try again.</p>";
                });
        });
    </script>
</body>
</html>
