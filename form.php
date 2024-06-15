

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $Name = $_POST['Name'];
    $Email = $_POST['Email'];
    $Message = $_POST['Message'];

    $conn = new mysqli('localhost', 'root', '', 'test1');
    if ($conn->connect_error) {
        die('Connection failed: ' . $conn->connect_error);
    } else {
        $stmt = $conn->prepare("INSERT INTO form (Name, Email, Message) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $Name, $Email, $Message);

        if ($stmt->execute()) {
            echo "Thank You!";
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
        $conn->close();
    }
}
?>