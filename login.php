<?php
include 'config.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = sanitize_input($_POST['username']);
    $password = sanitize_input($_POST['password']);
    
    $query = "SELECT * FROM Users WHERE username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role'];
            header("Location: index.php");
            exit();
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "Invalid username.";
    }
}

function sanitize_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: sans-serif;
            background: #121212;
            color: #ffffff;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .login-box {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 400px;
            padding: 40px;
            transform: translate(-50%, -50%);
            background: rgba(0,0,0,.5);
            box-shadow: 0 15px 25px rgba(0, 0, 0, 0.5);
            border-radius: 10px;
            text-align: center;
        }

        .login-box h1 {
            margin: 0 0 30px;
            padding: 0;
            color: #ffffff;
            text-align: center;
        }

        .login-box .form-group {
            position: relative;
            margin-bottom: 30px;
        }

        .login-box .form-group input {
            width: 100%;
            padding: 10px 0;
            font-size: 16px;
            color: #ffffff;
            border: none;
            border-bottom: 1px solid #ffffff;
            outline: none;
            background: transparent;
        }

        .login-box .form-group label {
            position: absolute;
            top: 0;
            left: 0;
            padding: 10px 0;
            font-size: 16px;
            color: #ffffff;
            pointer-events: none;
            transition: 0.5s;
        }

        .login-box .form-group input:focus ~ label,
        .login-box .form-group input:valid ~ label {
            top: -20px;
            left: 0;
            color: #03e9f4;
            font-size: 12px;
        }

        .login-box .btn {
            padding: 10px 20px;
            background: none;
            border: none;
            color: #03e9f4;
            font-size: 16px;
            text-decoration: none;
            text-transform: uppercase;
            overflow: hidden;
            margin-top: 40px;
            letter-spacing: 4px;
            cursor: pointer;
            transition: 0.5s;
        }

        .login-box .btn:hover {
            background: #03e9f4;
            color: #fff;
            border-radius: 5px;
            box-shadow: 0 0 5px #03e9f4,
                        0 0 25px #03e9f4,
                        0 0 50px #03e9f4,
                        0 0 100px #03e9f4;
        }

        .login-box .btn span {
            position: absolute;
            display: block;
        }

        .login-box .btn span:nth-child(1) {
            top: 0;
            left: 100%;
            width: 100%;
            height: 2px;
            background: linear-gradient(90deg, transparent, #03e9f4);
            animation: btn-anim1 1s linear infinite;
        }

        @keyframes btn-anim1 {
            0% {
                left: 100%;
            }
            50%, 100% {
                left: 0;
            }
        }

        .login-box .btn span:nth-child(2) {
            top: -100%;
            right: 0;
            width: 2px;
            height: 100%;
            background: linear-gradient(180deg, transparent, #03e9f4);
            animation: btn-anim2 1s linear infinite;
            animation-delay: 0.25s;
        }

        @keyframes btn-anim2 {
            0% {
                top: -100%;
            }
            50%, 100% {
                top: 0;
            }
        }

        .login-box .btn span:nth-child(3) {
            bottom: 0;
            right: 100%;
            width: 100%;
            height: 2px;
            background: linear-gradient(270deg, transparent, #03e9f4);
            animation: btn-anim3 1s linear infinite;
            animation-delay: 0.5s;
        }

        @keyframes btn-anim3 {
            0% {
                right: 100%;
            }
            50%, 100% {
                right: 0;
            }
        }

        .login-box .btn span:nth-child(4) {
            bottom: -100%;
            left: 0;
            width: 2px;
            height: 100%;
            background: linear-gradient(360deg, transparent, #03e9f4);
            animation: btn-anim4 1s linear infinite;
            animation-delay: 0.75s;
        }

        @keyframes btn-anim4 {
            0% {
                bottom: -100%;
            }
            50%, 100% {
                bottom: 0;
            }
        }

        .alert {
            color: #ff4d4d;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="login-box">
        <h1>Login</h1>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger" role="alert">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        <form action="" method="POST">
            <div class="form-group">
                <input type="text" id="username" name="username" required>
                <label for="username">Username</label>
            </div>
            <div class="form-group">
                <input type="password" id="password" name="password" required>
                <label for="password">Password</label>
            </div>
            <a href="#" class="btn">
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <button type="submit" style="background: none; border: none; color: inherit; font: inherit; cursor: pointer; outline: inherit;">Login</button>
            </a>
        </form>
    </div>
</body>
</html>
