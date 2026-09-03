<?php

require_once 'config/db.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($name === '' || $username === '' || $password === '') {

        $error = 'Sabhi fields required hain.';

    } elseif (strlen($password) < 6) {

        $error = 'Password kam se kam 6 characters ka hona chahiye.';

    } else {

        $check = $conn->prepare("
            SELECT id
            FROM admin_users
            WHERE username = ?
            LIMIT 1
        ");

        $check->bind_param('s', $username);
        $check->execute();

        $exists = $check->get_result()->fetch_assoc();

        $check->close();

        if ($exists) {

            $error = 'Ye username already exist karta hai.';

        } else {

            $hash = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            $stmt = $conn->prepare("
                INSERT INTO admin_users
                (name, username, password, status)
                VALUES (?, ?, ?, 'Active')
            ");

            if (!$stmt) {

                $error = $conn->error;

            } else {

                $stmt->bind_param(
                    'sss',
                    $name,
                    $username,
                    $hash
                );

                if ($stmt->execute()) {

                    $message =
                        'Admin account successfully create ho gaya. Ab create_admin.php ko delete kar dein.';

                } else {

                    $error =
                        $stmt->error;
                }

                $stmt->close();
            }
        }
    }
}

?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Create Admin | Gulabi Alibrose</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body{
    min-height:100vh;
    display:flex;
    align-items:center;
    justify-content:center;
    background:linear-gradient(135deg,#fff4f9,#fff,#fff0f7);
    font-family:system-ui,sans-serif;
}
.box{
    width:min(450px,92vw);
    background:#fff;
    border-radius:25px;
    padding:30px;
    box-shadow:0 25px 70px rgba(105,16,51,.12);
}
h2{font-weight:900;color:#681033}
.btn-pink{
    background:#d81b72;
    color:#fff;
    border:0;
}
.btn-pink:hover{
    background:#b8145f;
    color:#fff;
}
</style>
</head>
<body>

<div class="box">

    <div class="text-center mb-4">
        <h2>Gulabi Alibrose</h2>
        <small class="text-muted">
            Create Admin Account
        </small>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <?php if ($message): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <form method="post">

        <div class="mb-3">
            <label class="form-label">Admin Name</label>
            <input
                type="text"
                name="name"
                class="form-control"
                required
            >
        </div>

        <div class="mb-3">
            <label class="form-label">Username</label>
            <input
                type="text"
                name="username"
                class="form-control"
                required
            >
        </div>

        <div class="mb-4">
            <label class="form-label">Password</label>
            <input
                type="password"
                name="password"
                class="form-control"
                minlength="6"
                required
            >
        </div>

        <button class="btn btn-pink w-100 py-2">
            Create Admin
        </button>

    </form>

</div>

</body>
</html>
