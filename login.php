<?php
// Session Cookie Security Enhancements
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_samesite', 'Strict');
session_start();
require_once 'db.php';

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($email) && !empty($password)) {
        $stmt = $conn->prepare("SELECT id, name, email, password, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Verify password
            if (password_verify($password, $user['password'])) {
                // Secure session generation
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

                $userName = addslashes($user['name']);
                
                // Check Role and Redirect
                if (strtolower(trim($user['role'])) === 'admin') {
                    echo "<script>
                        alert('Welcome Admin, " . $userName . "!'); 
                        window.location.href = 'Backend/Dashboard.php';
                    </script>";
                } else {
                    echo "<script>
                        alert('Login Successful! Welcome " . $userName . "'); 
                        window.location.href = 'index.php';
                    </script>";
                }
                exit();
            } else {
                $error = "Invalid password!";
            }
        } else {
            $error = "No account found with this email!";
        }
    } else {
        $error = "Please fill in all fields!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduVault | Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --nsbm-green: #00a859;
            --nsbm-green-hover: #008a49;
            --nsbm-navy: #0b1d3a;
            --primary-bg: #f4f7fe;
            --card-bg: #ffffff;
            --text-main: #2b3674;
            --text-muted: #a3aed1;
            --border-color: #e0e5f2;
        }
        * {
            margin: 0; padding: 0; box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        body {
            background-color: var(--primary-bg); min-height: 100vh;
            display: flex; justify-content: center; align-items: center;
            color: var(--text-main); padding: 20px; overflow: hidden;
        }
        @keyframes slideUpFade {
            0% { opacity: 0; transform: translateY(60px); }
            100% { opacity: 1; transform: translateY(0); }
        }
        .login-card {
            background: var(--card-bg); width: 100%; max-width: 400px;
            padding: 40px 30px; border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05); border: 1px solid var(--border-color);
            animation: slideUpFade 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        .login-header { text-align: center; margin-bottom: 25px; }
        .login-header h2 { font-size: 28px; margin-bottom: 5px; font-weight: 800; }
        .edu-text { color: var(--nsbm-green); }
        .vault-text { color: var(--nsbm-navy); }
        .login-header p { color: var(--text-muted); font-size: 13px; line-height: 1.4; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 8px; color: var(--text-main); }
        .input-box { display: flex; align-items: center; background: #f8fafc; border: 1px solid var(--border-color); border-radius: 12px; padding: 12px 15px; transition: border-color 0.3s ease; position: relative; }
        .input-box:focus-within { border-color: var(--nsbm-green); }
        .input-box i { color: var(--text-muted); margin-right: 10px; font-size: 14px; z-index: 2; }
        .input-box input { border: none; outline: none; background: transparent; width: 100%; font-size: 14px; color: var(--text-main); z-index: 2; }
        .pwd-container { overflow: hidden; }
        .lighthouse-beam {
            position: absolute; right: 35px; top: 50%; transform: translateY(-50%);
            width: 0; height: 35px; background: linear-gradient(to left, rgba(0, 168, 89, 0.35) 0%, rgba(0, 168, 89, 0) 100%);
            clip-path: polygon(100% 30%, 0 0, 0 100%, 100% 70%);
            transition: width 0.4s ease, opacity 0.4s ease; opacity: 0; pointer-events: none; z-index: 1;
        }
        .lighthouse-beam.active { width: calc(100% - 35px); opacity: 1; }
        .form-options { display: flex; justify-content: space-between; align-items: center; font-size: 12px; margin-bottom: 25px; }
        .remember-me { display: flex; align-items: center; gap: 6px; color: var(--text-muted); cursor: pointer; }
        .forgot-pwd { color: var(--nsbm-navy); text-decoration: none; font-weight: 600; transition: color 0.3s ease; }
        .forgot-pwd:hover { text-decoration: underline; color: var(--nsbm-green); }
        .btn-login { width: 100%; padding: 12px; background-color: var(--nsbm-green); color: white; border: none; border-radius: 12px; font-size: 15px; font-weight: 600; cursor: pointer; transition: background 0.3s ease, transform 0.1s ease; }
        .btn-login:hover { background-color: var(--nsbm-green-hover); }
        .btn-login:active { transform: scale(0.98); }
        .register-link { text-align: center; margin-top: 25px; font-size: 13px; color: var(--text-muted); }
        .register-link a { color: var(--nsbm-navy); font-weight: 600; text-decoration: none; transition: color 0.3s ease; }
        .register-link a:hover { text-decoration: underline; color: var(--nsbm-green); }
        @media screen and (max-width: 480px) {
            body { padding: 15px; }
            .login-card { padding: 30px 20px; border-radius: 15px; }
            .login-header h2 { font-size: 24px; }
            .input-box { padding: 10px 12px; }
            .btn-login { padding: 10px; font-size: 14px; }
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="login-header">
            <h2><span class="edu-text">Edu</span><span class="vault-text">Vault</span></h2>
            <p>Welcome back! Please login to your account.</p>
        </div>

        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="email">Email Address</label>
                <div class="input-box">
                    <i class="fas fa-envelope"></i>
                    <input type="email" id="email" name="email" placeholder="Enter your email here" required>
                </div>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-box pwd-container">
                    <i class="fas fa-lock"></i>
                    <input type="password" id="password" name="password" placeholder="••••••••" required>
                    <i class="fas fa-eye" id="toggle-pwd" style="cursor:pointer; margin-left:auto; z-index:2; color: var(--text-muted);"></i>
                    <div id="lighthouse-beam" class="lighthouse-beam"></div>
                </div>
            </div>

            <div class="form-options">
                <label class="remember-me">
                    <input type="checkbox" name="remember"> Remember me
                </label>
                <a href="forgot.php" class="forgot-pwd">Forgot password?</a>
            </div>

            <button type="submit" class="btn-login">Sign In</button>
        </form>

        <div class="register-link">
            Don't have an account? <a href="register.php">Register</a>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const toggleIcon = document.getElementById('toggle-pwd');
            const pwdInput = document.getElementById('password');
            const beam = document.getElementById('lighthouse-beam');

            if (toggleIcon && pwdInput) {
                toggleIcon.addEventListener('click', () => {
                    const isPwd = pwdInput.getAttribute('type') === 'password';
                    pwdInput.setAttribute('type', isPwd ? 'text' : 'password');
                    toggleIcon.className = isPwd ? 'fas fa-eye-slash' : 'fas fa-eye';
                    if (beam) beam.classList.toggle('active', isPwd);
                });
            }
        });
    </script>
    <?php if ($error !== null): ?>
    <script>alert(<?= json_encode($error) ?>);</script>
    <?php endif; ?>
</body>
</html>