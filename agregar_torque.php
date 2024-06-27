<?php
include 'config.php';
include 'header.php';
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $torqueID = sanitize_input($_POST['torque_id']);
    $fechaAlta = sanitize_input($_POST['fecha_alta']);
    $torque = sanitize_input($_POST['torque']);
    $SN = sanitize_input($_POST['sn']);
    
    $target_dir = "pictures/";
    $target_file = $target_dir . basename($_FILES["foto"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    
    // Check if image file is a actual image or fake image
    $check = getimagesize($_FILES["foto"]["tmp_name"]);
    if ($check !== false) {
        $uploadOk = 1;
    } else {
        echo "File is not an image.";
        $uploadOk = 0;
    }
    
    // Check file size
    if ($_FILES["foto"]["size"] > 3000000) { // 3MB
        echo "Sorry, your file is too large.";
        $uploadOk = 0;
    }
    
    // Allow certain file formats
    if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
        echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        $uploadOk = 0;
    }
    
    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        echo "Sorry, your file was not uploaded.";
    // if everything is ok, try to upload file
    } else {
        if (move_uploaded_file($_FILES["foto"]["tmp_name"], $target_file)) {
            // File is uploaded successfully
            $foto = $target_file;
            
            // Insert into database
            $query = "INSERT INTO Torques (torqueID, fechaAlta, foto, torque, SN) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('sssds', $torqueID, $fechaAlta, $foto, $torque, $SN);
            $stmt->execute();

            header("Location: index.php");
            exit();
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    }
}

function sanitize_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}
?>

<main class="container mt-5">
    <h1 class="mb-4">Agregar Torque</h1>
    <form action="" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="torque_id">Torque ID:</label>
            <input type="text" id="torque_id" name="torque_id" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="fecha_alta">Fecha de Alta:</label>
            <input type="date" id="fecha_alta" name="fecha_alta" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="foto">Foto:</label>
            <input type="file" id="foto" name="foto" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="torque">Torque (Kgf/cm):</label>
            <input type="number" id="torque" name="torque" step="0.01" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="sn">SN:</label>
            <input type="text" id="sn" name="sn" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Agregar</button>
    </form>
</main>

<?php include 'footer.php'; ?>
