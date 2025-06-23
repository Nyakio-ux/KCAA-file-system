<?php
require_once 'includes/Database.php';

class FileManager {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function getUploadedFiles() {
        try {
            $conn = $this->db->connect();
            $stmt = $conn->prepare("SELECT * FROM uploaded_files ORDER BY uploaded_at DESC"); // Replace 'uploaded_files' with your table name
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching uploaded files: " . $e->getMessage());
            return [];
        }
    }
}

$fileManager = new FileManager();
$uploadedFiles = $fileManager->getUploadedFiles();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Uploaded Files</title>
    <link rel="stylesheet" href="styles.css"> <!-- Add your CSS file here -->
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

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #007BFF;
            color: white;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        a {
            color: #007BFF;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <h1>Uploaded Files</h1>
    <table>
        <thead>
            <tr>
                <th>File Name</th>
                <th>Department</th>
                <th>Originator</th>
                <th>Destination</th>
                <th>Receiver</th>
                <th>Date Uploaded</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($uploadedFiles)): ?>
                <?php foreach ($uploadedFiles as $file): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($file['file_name']); ?></td>
                        <td><?php echo htmlspecialchars($file['department']); ?></td>
                        <td><?php echo htmlspecialchars($file['originator']); ?></td>
                        <td><?php echo htmlspecialchars($file['destination']); ?></td>
                        <td><?php echo htmlspecialchars($file['receiver']); ?></td>
                        <td><?php echo htmlspecialchars($file['uploaded_at']); ?></td>
                        <td>
                            <a href="uploads/<?php echo htmlspecialchars($file['file_name']); ?>" target="_blank">View</a> |
                            <a href="delete_file.php?id=<?php echo htmlspecialchars($file['id']); ?>" onclick="return confirm('Are you sure you want to delete this file?');">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" style="text-align: center;">No files uploaded yet.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>