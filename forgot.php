<?php
// Session Cookie Security Settings
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
session_start();
require_once 'db.php';

$step = $_REQUEST['step'] ?? 'send_otp';
$gasUrl = 'https://script.google.com/macros/s/AKfycby4R3XWHYLjs1yD8qmyxF-wJDt79bPepZ6CKMe5NCqcy3TwJyi7cV1KTUXoJsAMx_E9/exec';

// --- STEP 1: REQUEST PASSWORD RESET OTP ---
if ($step === 'send_otp' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);

    $stmt = $conn->prepare("SELECT id, name FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo "<script>alert('No account found with this email address!'); window.location.href='forgot.php';</script>";
        exit();
    }

    $user = $result->fetch_assoc();
    $otp = rand(100000, 999999);

    $_SESSION['reset_email'] = $email;
    $_SESSION['reset_otp'] = $otp;
    $_SESSION['reset_expiry'] = time() + 300; // 5 Minutes Expiry
    $_SESSION['reset_attempts'] = 0; // Initialize OTP attempt counter

    // Client-side fetch to bypass free-hosting restrictions
    echo "
    <!DOCTYPE html>
    <html>
    <head><title>Sending Reset OTP...</title><meta name='viewport' content='width=device-width, initial-scale=1'></head>
    <body style='background:#f4f7fe; color:#2b3674; text-align:center; padding-top:20vh; font-family:sans-serif;'>
        <h2 style='color:#4318FF;'>EduVault is processing...</h2>
        <p>Sending password reset email to {$email}. Please wait...</p>
        <script>
            setTimeout(() => {
                fetch('{$gasUrl}', {
                    method: 'POST',
                    mode: 'no-cors',
                    body: JSON.stringify({ type: 'forgot', email: '{$email}', name: '{$user['name']}', otp: '{$otp}' })
                });
                
                setTimeout(() => {
                    alert('Password reset OTP code sent to your inbox!');
                    window.location.href='forgot.php?step=verify';
                }, 1500);
            }, 500);
        </script>
    </body>
    </html>
    ";
    exit();
}

// --- STEP 2: VERIFY OTP & RESET PASSWORD ---
if ($step === 'reset_password' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $entered_otp = trim($_POST['otp_code']);
    $new_password = $_POST['new_password'];

    // Check if session exists
    if (!isset($_SESSION['reset_otp']) || !isset($_SESSION['reset_expiry'])) {
        echo "<script>alert('Session expired. Please try again.'); window.location.href='forgot.php';</script>";
        exit();
    }

    // Check if OTP code has expired
    if (time() > $_SESSION['reset_expiry']) {
        unset($_SESSION['reset_otp'], $_SESSION['reset_email'], $_SESSION['reset_expiry'], $_SESSION['reset_attempts']);
        echo "<script>alert('OTP Code Expired!'); window.location.href='forgot.php';</script>";
        exit();
    }

    // Verify OTP
    if ($entered_otp == $_SESSION['reset_otp']) {
        $hashed_pwd = password_hash($new_password, PASSWORD_BCRYPT);
        $email = $_SESSION['reset_email'];

        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
        $stmt->bind_param("ss", $hashed_pwd, $email);

        if ($stmt->execute()) {
            // Auto login after password reset
            $stmt_user = $conn->prepare("SELECT id, name, role FROM users WHERE email = ?");
            $stmt_user->bind_param("s", $email);
            $stmt_user->execute();
            $res_user = $stmt_user->get_result()->fetch_assoc();

            session_regenerate_id(true); // Secure session ID regeneration
            $_SESSION['user_id'] = $res_user['id'];
            $_SESSION['user_name'] = $res_user['name'];
            $_SESSION['user_role'] = $res_user['role'];
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

            // Clear reset tokens
            unset($_SESSION['reset_otp'], $_SESSION['reset_email'], $_SESSION['reset_expiry'], $_SESSION['reset_attempts']);

            // Role-based redirection
            if ($res_user['role'] === 'admin') {
                $adminName = addslashes($res_user['name']);
                echo "<script>alert('Password Reset Successful! Welcome Admin, " . $adminName . "'); window.location.href='Backend/Dashboard.php';</script>";
            } else {
                echo "<script>alert('Password Reset Successful! Redirecting to Dashboard...'); window.location.href='index.php';</script>";
            }
        } else {
            echo "<script>alert('Database Error!'); window.location.href='forgot.php';</script>";
        }
    } else {
        // Increment attempt counter on failure
        $_SESSION['reset_attempts']++;
        if ($_SESSION['reset_attempts'] >= 3) {
            unset($_SESSION['reset_otp'], $_SESSION['reset_email'], $_SESSION['reset_expiry'], $_SESSION['reset_attempts']);
            echo "<script>alert('Maximum attempts reached. Session cleared. Please request a new OTP code.'); window.location.href='forgot.php';</script>";
        } else {
            $rem = 3 - $_SESSION['reset_attempts'];
            echo "<script>alert('Invalid OTP Code! $rem attempt(s) remaining.'); window.location.href='forgot.php?step=verify';</script>";
        }
    }
    exit();
}

$isVerifyStep = ($step === 'verify');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduVault | Forgot Password</title>
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
            margin: 0; 
            padding: 0; 
            box-sizing: border-box; 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
        }

        body { 
            background-color: var(--primary-bg); 
            min-height: 100vh; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            color: var(--text-main); 
            padding: 20px; 
        }

        .forgot-card { 
            background: var(--card-bg); 
            width: 100%; 
            max-width: 400px; 
            padding: 40px 30px; 
            border-radius: 20px; 
            box-shadow: 0 10px 25px rgba(0,0,0,0.05); 
            border: 1px solid var(--border-color); 
        }

        .forgot-header { 
            text-align: center; 
            margin-bottom: 25px; 
        }

        .forgot-header h2 { 
            font-size: 28px; 
            margin-bottom: 5px; 
            font-weight: 800; 
        }

        .edu-text { color: var(--nsbm-green); }
        .vault-text { color: var(--nsbm-navy); }

        .forgot-header p { 
            color: var(--text-muted); 
            font-size: 13px; 
            line-height: 1.4; 
        }

        .form-group { 
            margin-bottom: 20px; 
        }

        .form-group label { 
            display: block; 
            font-size: 13px; 
            font-weight: 600; 
            margin-bottom: 8px; 
            color: var(--text-main); 
        }

        .input-box { 
            display: flex; 
            align-items: center; 
            background: #f8fafc; 
            border: 1px solid var(--border-color); 
            border-radius: 12px; 
            padding: 12px 15px; 
            transition: border-color 0.3s ease; 
        }

        .input-box:focus-within { 
            border-color: var(--nsbm-green); 
        }

        .input-box i { 
            color: var(--text-muted); 
            margin-right: 10px; 
            font-size: 14px; 
        }

        .input-box input { 
            border: none; 
            outline: none; 
            background: transparent; 
            width: 100%; 
            font-size: 14px; 
            color: var(--text-main); 
        }

        .btn-reset { 
            width: 100%; 
            padding: 12px; 
            background-color: var(--nsbm-green); 
            color: white; 
            border: none; 
            border-radius: 12px; 
            font-size: 15px; 
            font-weight: 600; 
            cursor: pointer; 
            transition: background 0.3s ease, transform 0.1s ease; 
        }

        .btn-reset:hover { 
            background-color: var(--nsbm-green-hover); 
        }

        .btn-reset:active { 
            transform: scale(0.98); 
        }

        .back-link { 
            text-align: center; 
            margin-top: 25px; 
            font-size: 13px; 
        }

        .back-link a { 
            color: var(--nsbm-navy); 
            font-weight: 600; 
            text-decoration: none; 
            transition: color 0.3s ease; 
        }

        .back-link a:hover { 
            text-decoration: underline; 
            color: var(--nsbm-green); 
        }

        @media screen and (max-width: 480px) { 
            body { padding: 15px; } 
            .forgot-card { padding: 30px 20px; border-radius: 15px; } 
            .forgot-header h2 { font-size: 24px; } 
            .input-box { padding: 10px 12px; } 
            .btn-reset { padding: 10px; font-size: 14px; } 
        }
    </style>
</head>
<body>

    <div class="forgot-card">
        <div class="forgot-header">
            <h2><span class="edu-text">Edu</span><span class="vault-text">Vault</span></h2>
            <p id="form-subtitle">
                <?php echo $isVerifyStep ? "Enter the OTP code and your new password." : "Enter your email address to receive password reset instructions."; ?>
            </p>
        </div>

        <div id="form-container">
            <?php if ($isVerifyStep): ?>
                <form action="forgot.php" method="POST">
                    <input type="hidden" name="step" value="reset_password">
                    <div class="form-group">
                        <label>Verification OTP Code</label>
                        <div class="input-box">
                            <i class="fas fa-key"></i>
                            <input type="text" name="otp_code" maxlength="6" required placeholder="XXXXXX" style="letter-spacing: 5px; font-weight: bold; text-align: center;">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>New Password</label>
                        <div class="input-box">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="new_password" placeholder="Enter new password" required>
                        </div>
                    </div>
                    <button type="submit" class="btn-reset">Update Password</button>
                </form>
            <?php else: ?>
                <form action="forgot.php" method="POST">
                    <input type="hidden" name="step" value="send_otp">
                    <div class="form-group">
                        <label>Email Address</label>
                        <div class="input-box">
                            <i class="fas fa-envelope"></i>
                            <input type="email" name="email" placeholder="Your Email Here" required>
                        </div>
                    </div>
                    <button type="submit" class="btn-reset">Send Reset OTP</button>
                </form>
            <?php endif; ?>
        </div>

        <div class="back-link">
            Remembered your password? <a href="login.php">Back to Login</a>
        </div>
    </div>

</body>
</html>