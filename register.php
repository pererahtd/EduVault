<?php
// Session Cookie Security Enhancements
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
session_start();
require_once 'db.php';

$action = $_REQUEST['action'] ?? $_POST['action'] ?? '';

// Your NEW Google Apps Script Web App URL
$gasUrl = 'https://script.google.com/macros/s/AKfycby4R3XWHYLjs1yD8qmyxF-wJDt79bPepZ6CKMe5NCqcy3TwJyi7cV1KTUXoJsAMx_E9/exec';

// --- STEP 1: REQUEST OTP ---
if ($action === 'request_otp' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // DB Email Check
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        echo "<script>alert('Email is already registered!'); window.location.href='register.php';</script>";
        exit();
    }

    // Save OTP to Session
    $otp = rand(100000, 999999);
    $_SESSION['reg_otp'] = $otp;
    $_SESSION['otp_expiry'] = time() + 300; // 5 Minutes expiry
    $_SESSION['otp_attempts'] = 0;
    $_SESSION['reg_data'] = [
        'fullname' => $fullname,
        'email' => $email,
        'password' => password_hash($password, PASSWORD_BCRYPT)
    ];

    // Client-Side Fetch (This worked for you previously)
    echo "
    <!DOCTYPE html>
    <html>
    <head><title>Sending OTP...</title><meta name='viewport' content='width=device-width, initial-scale=1'></head>
    <body style='background:#f4f7fe; color:#2b3674; text-align:center; padding-top:20vh; font-family:sans-serif;'>
        <h2 style='color:#00a859;'>EduVault is processing...</h2>
        <p>Sending verification email to {$email}. Please wait...</p>
        <script>
            setTimeout(() => {
                fetch('{$gasUrl}', {
                    method: 'POST',
                    mode: 'no-cors',
                    body: JSON.stringify({ type: 'register', email: '{$email}', name: '{$fullname}', otp: '{$otp}' })
                });
                
                setTimeout(() => {
                    alert('OTP code successfully sent to your Gmail inbox!');
                    window.location.href='register.php?step=verify';
                }, 1500);
            }, 500);
        </script>
    </body>
    </html>
    ";
    exit();
}

// --- STEP 2: VERIFY OTP ---
if ($action === 'verify_otp' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $entered_otp = trim($_POST['otp_code']);

    if (!isset($_SESSION['reg_otp']) || time() > $_SESSION['otp_expiry']) {
        echo "<script>alert('OTP Session Expired!'); window.location.href='register.php';</script>";
        exit();
    }

    if ($entered_otp == $_SESSION['reg_otp']) {
        $data = $_SESSION['reg_data'];

        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'student')");
        $stmt->bind_param("sss", $data['fullname'], $data['email'], $data['password']);

        if ($stmt->execute()) {
            $_SESSION['user_id'] = $conn->insert_id;
            $_SESSION['user_name'] = $data['fullname'];

            unset($_SESSION['reg_otp'], $_SESSION['otp_expiry'], $_SESSION['reg_data'], $_SESSION['otp_attempts']);

            echo "<script>alert('Verification Successful! Redirecting to Dashboard...'); window.location.href='index.php';</script>";
        } else {
            echo "<script>alert('Database Error!'); window.location.href='register.php';</script>";
        }
    } else {
        $_SESSION['otp_attempts']++;
        if ($_SESSION['otp_attempts'] >= 3) {
            unset($_SESSION['reg_otp'], $_SESSION['otp_expiry'], $_SESSION['reg_data'], $_SESSION['otp_attempts']);
            echo "<script>alert('Maximum attempts reached. Session cleared. Please register again.'); window.location.href='register.php';</script>";
        } else {
            $rem = 3 - $_SESSION['otp_attempts'];
            echo "<script>alert('Invalid OTP Code! $rem attempts remaining.'); window.location.href='register.php?step=verify';</script>";
        }
    }
    exit();
}

$isVerifyStep = (($_GET['step'] ?? '') === 'verify');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduVault | Student Register</title>
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
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: var(--primary-bg); min-height: 100vh; display: flex; justify-content: center; align-items: center; color: var(--text-main); padding: 20px; }
        .register-card { background: var(--card-bg); width: 100%; max-width: 420px; padding: 40px 30px; border-radius: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); border: 1px solid var(--border-color); }
        .register-header { text-align: center; margin-bottom: 25px; }
        .register-header h2 { font-size: 28px; margin-bottom: 5px; font-weight: 800; }
        .edu-text { color: var(--nsbm-green); }
        .vault-text { color: var(--nsbm-navy); }
        .register-header p { color: var(--text-muted); font-size: 13px; line-height: 1.4; }
        .form-group { margin-bottom: 18px; }
        .form-group label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: var(--text-main); }
        .input-box { display: flex; align-items: center; background: #f8fafc; border: 1px solid var(--border-color); border-radius: 12px; padding: 12px 15px; transition: border-color 0.3s ease; position: relative; }
        .input-box:focus-within { border-color: var(--nsbm-green); }
        .input-box i { color: var(--text-muted); margin-right: 10px; font-size: 14px; z-index: 2; }
        .input-box input { border: none; outline: none; background: transparent; width: 100%; font-size: 14px; color: var(--text-main); z-index: 2; }
        .pwd-container { overflow: hidden; }
        .lighthouse-beam { position: absolute; right: 35px; top: 50%; transform: translateY(-50%); width: 0; height: 35px; background: linear-gradient(to left, rgba(0, 168, 89, 0.35) 0%, rgba(0, 168, 89, 0) 100%); clip-path: polygon(100% 30%, 0 0, 0 100%, 100% 70%); transition: width 0.4s ease, opacity 0.4s ease; opacity: 0; pointer-events: none; z-index: 1; }
        .lighthouse-beam.active { width: calc(100% - 35px); opacity: 1; }
        .btn-register { width: 100%; padding: 12px; background-color: var(--nsbm-green); color: white; border: none; border-radius: 12px; font-size: 15px; font-weight: 600; cursor: pointer; transition: background 0.3s ease, transform 0.1s ease; margin-top: 10px; }
        .btn-register:hover { background-color: var(--nsbm-green-hover); }
        .btn-register:active { transform: scale(0.98); }
        .login-link { text-align: center; margin-top: 25px; font-size: 13px; color: var(--text-muted); }
        .login-link a { color: var(--nsbm-navy); font-weight: 600; text-decoration: none; transition: color 0.3s ease; }
        .login-link a:hover { text-decoration: underline; color: var(--nsbm-green); }
        @media screen and (max-width: 480px) { body { padding: 15px; } .register-card { padding: 30px 20px; border-radius: 15px; } .register-header h2 { font-size: 24px; } .input-box { padding: 10px 12px; } .btn-register { padding: 10px; font-size: 14px; } }
    </style>
</head>
<body>
    <div class="register-card">
        <div class="register-header">
            <h2><span class="edu-text">Edu</span><span class="vault-text">Vault</span></h2>
            <p id="form-subtitle">
                <?php echo $isVerifyStep ? "Enter the 6-digit OTP code sent to your email" : "Create your student account"; ?>
            </p>
        </div>

        <div id="form-container">
            <?php if ($isVerifyStep): ?>
                <form action="register.php" method="POST">
                    <input type="hidden" name="action" value="verify_otp">
                    <div class="form-group">
                        <label>Verification OTP Code</label>
                        <div class="input-box">
                            <i class="fas fa-key"></i>
                            <input type="text" name="otp_code" maxlength="6" required placeholder="XXXXXX" style="letter-spacing: 6px; font-weight: bold; text-align: center; font-size: 16px;">
                        </div>
                    </div>
                    <button type="submit" class="btn-register">Verify OTP & Complete</button>
                </form>
            <?php else: ?>
                <form action="register.php" method="POST" onsubmit="return checkPwd()">
                    <input type="hidden" name="action" value="request_otp">
                    <div class="form-group">
                        <label>Full Name</label>
                        <div class="input-box">
                            <i class="fas fa-user"></i>
                            <input type="text" name="fullname" placeholder="e.g. Kasun Perera" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Email Address</label>
                        <div class="input-box">
                            <i class="fas fa-envelope"></i>
                            <input type="email" name="email" placeholder="Enter your email here" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <div class="input-box pwd-container">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="pwd" name="password" placeholder="••••••••" required>
                            <i class="fas fa-eye" id="toggle-pwd-1" style="cursor:pointer; margin-left:auto; z-index:2; color: var(--text-muted);"></i>
                            <div id="lighthouse-beam-1" class="lighthouse-beam"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Confirm Password</label>
                        <div class="input-box pwd-container">
                            <i class="fas fa-shield-alt"></i>
                            <input type="password" id="cpwd" name="confirm_password" placeholder="••••••••" required>
                            <i class="fas fa-eye" id="toggle-pwd-2" style="cursor:pointer; margin-left:auto; z-index:2; color: var(--text-muted);"></i>
                            <div id="lighthouse-beam-2" class="lighthouse-beam"></div>
                        </div>
                    </div>
                    <button type="submit" class="btn-register">Send Verification OTP</button>
                </form>
            <?php endif; ?>
        </div>

        <div class="login-link">
            Already have an account? <a href="login.php">Sign In</a>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const t1 = document.getElementById('toggle-pwd-1'), p1 = document.getElementById('pwd'), b1 = document.getElementById('lighthouse-beam-1');
            if (t1 && p1) { 
                t1.addEventListener('click', () => { 
                    const isPwd = p1.getAttribute('type') === 'password'; 
                    p1.setAttribute('type', isPwd ? 'text' : 'password'); 
                    t1.className = isPwd ? 'fas fa-eye-slash' : 'fas fa-eye'; 
                    if (b1) b1.classList.toggle('active', isPwd); 
                }); 
            }
            
            const t2 = document.getElementById('toggle-pwd-2'), p2 = document.getElementById('cpwd'), b2 = document.getElementById('lighthouse-beam-2');
            if (t2 && p2) { 
                t2.addEventListener('click', () => { 
                    const isPwd = p2.getAttribute('type') === 'password'; 
                    p2.setAttribute('type', isPwd ? 'text' : 'password'); 
                    t2.className = isPwd ? 'fas fa-eye-slash' : 'fas fa-eye'; 
                    if (b2) b2.classList.toggle('active', isPwd); 
                }); 
            }
        });

        function checkPwd() {
            const pwd = document.getElementById('pwd').value;
            const cpwd = document.getElementById('cpwd').value;
            if (pwd !== cpwd) {
                alert("Passwords do not match!");
                return false;
            }
            return true;
        }
    </script>
</body>
</html>