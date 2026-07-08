<?php
session_start();

// Redirect if already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: index.php');
    exit;
}

$error = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($username === 'admin' && $password === 'admin123') {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = 'admin';
        header('Location: index.php');
        exit;
    } else {
        $error = '用户名或密码错误';
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理员登录 - 卤肉 重庆特色</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=ZCOOL+XiaoWei&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'ZCOOL XiaoWei', serif;
            background: linear-gradient(135deg, #1a0a0a 0%, #3d1f1f 50%, #1a0a0a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            background: linear-gradient(145deg, #8b0000 0%, #5c0000 100%);
            border-radius: 20px;
            padding: 50px 40px;
            width: 100%;
            max-width: 420px;
            box-shadow: 
                0 25px 50px rgba(0, 0, 0, 0.5),
                0 0 100px rgba(218, 165, 32, 0.2),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
            border: 2px solid #daa520;
            position: relative;
            overflow: hidden;
        }

        .login-container::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(218, 165, 32, 0.1) 0%, transparent 70%);
            animation: shimmer 8s linear infinite;
        }

        @keyframes shimmer {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .login-content {
            position: relative;
            z-index: 1;
        }

        .logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo h1 {
            color: #daa520;
            font-size: 32px;
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
            letter-spacing: 3px;
        }

        .logo p {
            color: #f0d0a0;
            font-size: 14px;
            margin-top: 8px;
            letter-spacing: 2px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            color: #f0d0a0;
            font-size: 14px;
            margin-bottom: 8px;
            letter-spacing: 1px;
        }

        .form-group input {
            width: 100%;
            padding: 14px 18px;
            border: 2px solid #daa520;
            border-radius: 10px;
            background: rgba(0, 0, 0, 0.3);
            color: #fff;
            font-size: 16px;
            font-family: 'ZCOOL XiaoWei', serif;
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: #ffd700;
            box-shadow: 0 0 20px rgba(218, 165, 32, 0.4);
            background: rgba(0, 0, 0, 0.5);
        }

        .form-group input::placeholder {
            color: #888;
        }

        .error-message {
            background: rgba(255, 0, 0, 0.2);
            border: 1px solid #ff4444;
            color: #ff6666;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: center;
        }

        .submit-btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(145deg, #daa520 0%, #b8860b 100%);
            border: none;
            border-radius: 10px;
            color: #1a0a0a;
            font-size: 18px;
            font-weight: bold;
            font-family: 'ZCOOL XiaoWei', serif;
            cursor: pointer;
            transition: all 0.3s ease;
            text-shadow: 0 1px 0 rgba(255, 255, 255, 0.3);
            box-shadow: 0 4px 15px rgba(218, 165, 32, 0.3);
        }

        .submit-btn:hover {
            background: linear-gradient(145deg, #ffd700 0%, #daa520 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(218, 165, 32, 0.5);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        .decorative-line {
            height: 2px;
            background: linear-gradient(90deg, transparent, #daa520, transparent);
            margin: 25px 0;
        }

        .back-link {
            text-align: center;
            margin-top: 20px;
        }

        .back-link a {
            color: #f0d0a0;
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s ease;
        }

        .back-link a:hover {
            color: #daa520;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-content">
            <div class="logo">
                <h1>卤肉 重庆特色</h1>
                <p>管理系统登录</p>
            </div>

            <div class="decorative-line"></div>

            <?php if ($error): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">用户名</label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        placeholder="请输入用户名"
                        required 
                        autocomplete="username"
                    >
                </div>

                <div class="form-group">
                    <label for="password">密码</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        placeholder="请输入密码"
                        required 
                        autocomplete="current-password"
                    >
                </div>

                <button type="submit" class="submit-btn">登录</button>
            </form>

            <div class="back-link">
                <a href="../menu.php">← 返回菜单页面</a>
            </div>
        </div>
    </div>
</body>
</html>
