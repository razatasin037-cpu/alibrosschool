<?php
session_start();

if (isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

require_once __DIR__ . '/config/db.php';

$error = '';
$success = '';

if (isset($_GET['logout']) && $_GET['logout'] === '1') {
    $success = 'Aap successfully logout ho gaye.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {

        $error = 'Username aur password dono required hain.';

    } else {

        $stmt = $conn->prepare("
            SELECT id, name, username, password, status
            FROM admin_users
            WHERE username = ?
            LIMIT 1
        ");

        if (!$stmt) {

            $error = 'Database connection/query error.';

        } else {

            $stmt->bind_param('s', $username);
            $stmt->execute();

            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            $stmt->close();

            if (!$user) {

                $error = 'Username ya password galat hai.';

            } elseif ($user['status'] !== 'Active') {

                $error = 'Aapka admin account blocked hai.';

            } elseif (!password_verify($password, $user['password'])) {

                $error = 'Username ya password galat hai.';

            } else {

                session_regenerate_id(true);

                $_SESSION['admin_id'] = (int)$user['id'];
                $_SESSION['admin_name'] = $user['name'];
                $_SESSION['admin_username'] = $user['username'];
                $_SESSION['login_time'] = time();

                header('Location: dashboard.php');
                exit;
            }
        }
    }
}
?>

<!doctype html>
<html lang="en">

<head>

<meta charset="utf-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1"
>

<meta
    name="theme-color"
    content="#d81b72"
>

<title>
    Login | Gulabi Alibrose
</title>

<link
    rel="preconnect"
    href="https://fonts.googleapis.com"
>

<link
    rel="preconnect"
    href="https://fonts.gstatic.com"
    crossorigin
>

<link
    href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800;900&display=swap"
    rel="stylesheet"
>

<link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
>

<style>

:root {

    --pink: #d81b72;
    --pink2: #f04a9b;
    --dark: #681033;
    --soft: #fff0f7;
    --text: #24191f;
    --muted: #8c7c84;

}

* {
    box-sizing: border-box;
}

html,
body {

    margin: 0;
    min-height: 100%;

}

body {

    min-height: 100vh;

    display: flex;

    align-items: center;

    justify-content: center;

    overflow: hidden;

    font-family:
        "DM Sans",
        Arial,
        sans-serif;

    color: var(--text);

    background:
        radial-gradient(
            circle at 10% 15%,
            rgba(216,27,114,.16),
            transparent 28%
        ),
        radial-gradient(
            circle at 90% 85%,
            rgba(240,74,155,.18),
            transparent 30%
        ),
        linear-gradient(
            135deg,
            #fff8fb 0%,
            #fff 48%,
            #fff5f9 100%
        );

}


/* BACKGROUND */

.bg {

    position: fixed;

    inset: 0;

    overflow: hidden;

    pointer-events: none;

}

.orb {

    position: absolute;

    border-radius: 50%;

}

.orb.one {

    width: 330px;
    height: 330px;

    left: -150px;
    top: -130px;

    background:
        rgba(216,27,114,.12);

    animation:
        orbOne 9s ease-in-out infinite;

}

.orb.two {

    width: 280px;
    height: 280px;

    right: -120px;
    bottom: -100px;

    background:
        rgba(240,74,155,.13);

    animation:
        orbTwo 10s ease-in-out infinite;

}

.orb.three {

    width: 120px;
    height: 120px;

    right: 17%;
    top: 9%;

    border:
        1px solid
        rgba(216,27,114,.15);

    animation:
        float 6s ease-in-out infinite;

}

.orb.four {

    width: 80px;
    height: 80px;

    left: 13%;
    bottom: 13%;

    border:
        1px solid
        rgba(216,27,114,.13);

    animation:
        float 7s ease-in-out infinite reverse;

}

@keyframes orbOne {

    50% {
        transform:
            translate(45px,35px)
            scale(1.08);
    }

}

@keyframes orbTwo {

    50% {
        transform:
            translate(-40px,-30px)
            scale(1.08);
    }

}

@keyframes float {

    50% {
        transform:
            translateY(-18px)
            rotate(8deg);
    }

}


/* CARD */

.login-card {

    position: relative;

    z-index: 2;

    width: min(1080px,94vw);

    min-height: 640px;

    display: grid;

    grid-template-columns:
        1.03fr
        .97fr;

    overflow: hidden;

    border:
        1px solid
        rgba(255,255,255,.95);

    border-radius: 36px;

    background:
        rgba(255,255,255,.73);

    box-shadow:
        0 40px 100px
        rgba(105,16,51,.14),
        inset 0 1px 0
        rgba(255,255,255,.9);

    backdrop-filter:
        blur(22px);

    -webkit-backdrop-filter:
        blur(22px);

    animation:
        cardIn .9s
        cubic-bezier(.22,1,.36,1)
        both;

}

@keyframes cardIn {

    from {

        opacity: 0;

        transform:
            translateY(45px)
            scale(.96);

    }

    to {

        opacity: 1;

        transform: none;

    }

}


/* LEFT */

.visual {

    position: relative;

    overflow: hidden;

    display: flex;

    align-items: center;

    padding: 58px;

    color: white;

    background:
        linear-gradient(
            145deg,
            #5d0b2d 0%,
            #981047 38%,
            #d81b72 72%,
            #f04a9b 100%
        );

}

.visual::before {

    content: "";

    position: absolute;

    width: 470px;
    height: 470px;

    border-radius: 50%;

    right: -240px;
    top: -230px;

    border:
        1px solid
        rgba(255,255,255,.16);

    box-shadow:
        0 0 0 35px
        rgba(255,255,255,.035),
        0 0 0 70px
        rgba(255,255,255,.025);

    animation:
        visualCircle 15s
        linear infinite;

}

.visual::after {

    content: "";

    position: absolute;

    width: 390px;
    height: 390px;

    border-radius: 50%;

    left: -260px;
    bottom: -240px;

    background:
        rgba(255,255,255,.07);

}

@keyframes visualCircle {

    to {
        transform:
            rotate(360deg);
    }

}

.visual-content {

    position: relative;

    z-index: 3;

    width: 100%;

}

.logo-box {

    width: 80px;
    height: 80px;

    display: grid;

    place-items: center;

    border-radius: 25px;

    color: white;

    font-size: 34px;

    font-weight: 900;

    background:
        rgba(255,255,255,.13);

    border:
        1px solid
        rgba(255,255,255,.25);

    box-shadow:
        0 18px 40px
        rgba(0,0,0,.14),
        inset 0 1px 0
        rgba(255,255,255,.2);

    backdrop-filter:
        blur(12px);

    animation:
        logoFloat 4s
        ease-in-out infinite;

}

@keyframes logoFloat {

    50% {

        transform:
            translateY(-7px)
            rotate(2deg);

    }

}

.visual-kicker {

    margin-top: 30px;

    font-size: 10px;

    font-weight: 900;

    letter-spacing: 2px;

    opacity: .72;

}

.visual-title {

    margin:
        9px 0 17px;

    font-size: 48px;

    line-height: 1;

    font-weight: 900;

    letter-spacing: -1.8px;

}

.visual-description {

    max-width: 420px;

    margin: 0;

    color:
        rgba(255,255,255,.78);

    font-size: 13px;

    line-height: 1.9;

}

.features {

    display: grid;

    gap: 11px;

    margin-top: 31px;

}

.feature {

    display: flex;

    align-items: center;

    gap: 11px;

    color:
        rgba(255,255,255,.86);

    font-size: 11px;

    font-weight: 700;

}

.feature-icon {

    width: 32px;
    height: 32px;

    flex-shrink: 0;

    display: grid;

    place-items: center;

    border-radius: 11px;

    background:
        rgba(255,255,255,.11);

    border:
        1px solid
        rgba(255,255,255,.11);

}


/* RIGHT */

.form-side {

    display: flex;

    align-items: center;

    padding: 58px;

}

.form-inner {

    width: 100%;

    max-width: 400px;

    margin: auto;

}

.kicker {

    color: var(--pink);

    font-size: 10px;

    font-weight: 900;

    letter-spacing: 1.8px;

}

.title {

    margin:
        8px 0 7px;

    color: #21171c;

    font-size: 34px;

    line-height: 1.08;

    font-weight: 900;

    letter-spacing: -1px;

}

.subtitle {

    margin:
        0 0 30px;

    color: var(--muted);

    font-size: 12px;

    line-height: 1.7;

}

.message {

    display: flex;

    align-items: center;

    gap: 9px;

    margin-bottom: 17px;

    padding:
        12px 13px;

    border-radius: 13px;

    font-size: 11px;

    font-weight: 700;

    animation:
        messageIn .35s
        ease both;

}

.message.error {

    color: #a5164e;

    background: #fff0f5;

    border:
        1px solid
        #ffd7e7;

}

.message.success {

    color: #187044;

    background: #eefbf3;

    border:
        1px solid
        #ccefdc;

}

@keyframes messageIn {

    from {

        opacity: 0;

        transform:
            translateY(-7px);

    }

    to {

        opacity: 1;

        transform: none;

    }

}

.field {

    margin-bottom: 18px;

}

.label {

    display: block;

    margin:
        0 0 7px 2px;

    color: #454047;

    font-size: 10px;

    font-weight: 900;

}

.input-wrap {

    position: relative;

}

.input-icon {

    position: absolute;

    left: 15px;

    top: 50%;

    z-index: 2;

    color: #c31b64;

    font-size: 15px;

    transform:
        translateY(-50%);

}

.input {

    width: 100%;

    height: 54px;

    padding:
        0 48px 0 44px;

    outline: none;

    border:
        1px solid
        #e8e0e4;

    border-radius: 15px;

    background: #fff;

    color: #282126;

    font-family: inherit;

    font-size: 12px;

    transition: .3s ease;

}

.input::placeholder {

    color: #b3a9ae;

}

.input:hover {

    border-color:
        #efc4d7;

}

.input:focus {

    border-color:
        var(--pink);

    box-shadow:
        0 0 0 4px
        rgba(216,27,114,.08),
        0 8px 25px
        rgba(216,27,114,.06);

}

.toggle-password {

    position: absolute;

    right: 12px;

    top: 50%;

    width: 34px;
    height: 34px;

    display: grid;

    place-items: center;

    border: 0;

    border-radius: 10px;

    color: #8f858b;

    background: transparent;

    cursor: pointer;

    transform:
        translateY(-50%);

    transition: .25s ease;

}

.toggle-password:hover {

    color: var(--pink);

    background:
        var(--soft);

}

.login-btn {

    position: relative;

    width: 100%;

    height: 54px;

    margin-top: 5px;

    border: 0;

    border-radius: 15px;

    color: white;

    background:
        linear-gradient(
            100deg,
            #a81254,
            #d81b72,
            #f04a9b
        );

    box-shadow:
        0 14px 30px
        rgba(216,27,114,.22);

    font-family: inherit;

    font-size: 11px;

    font-weight: 900;

    cursor: pointer;

    overflow: hidden;

    transition: .35s ease;

}

.login-btn::after {

    content: "";

    position: absolute;

    top: 0;

    left: -100%;

    width: 65%;

    height: 100%;

    background:
        linear-gradient(
            90deg,
            transparent,
            rgba(255,255,255,.25),
            transparent
        );

    transition: .7s ease;

}

.login-btn:hover {

    transform:
        translateY(-3px);

    box-shadow:
        0 19px 38px
        rgba(216,27,114,.28);

}

.login-btn:hover::after {

    left: 135%;

}

.login-btn.loading {

    pointer-events: none;

    opacity: .86;

}

.login-btn .spinner {

    display: none;

    width: 15px;

    height: 15px;

    margin-right: 7px;

    vertical-align: -3px;

    border:
        2px solid
        rgba(255,255,255,.4);

    border-top-color:
        #fff;

    border-radius: 50%;

    animation:
        spin .7s
        linear infinite;

}

.login-btn.loading .spinner {

    display:
        inline-block;

}

@keyframes spin {

    to {
        transform:
            rotate(360deg);
    }

}

.security {

    display: flex;

    align-items: center;

    justify-content: center;

    gap: 6px;

    margin-top: 22px;

    color: #9b9196;

    font-size: 9px;

}

.security i {

    color:
        #d81b72;

}

.footer-text {

    margin-top: 10px;

    text-align: center;

    color: #b1a8ad;

    font-size: 8px;

}


/* RESPONSIVE */

@media(max-width:820px) {

    body {

        overflow: auto;

        padding: 20px 0;

    }

    .login-card {

        grid-template-columns: 1fr;

        width: min(520px,94vw);

        min-height: auto;

    }

    .visual {

        padding: 36px;

        min-height: 330px;

    }

    .visual-title {

        font-size: 38px;

    }

    .features {

        display: none;

    }

    .form-side {

        padding: 38px;

    }

}

@media(max-width:480px) {

    .login-card {

        width: 94vw;

        border-radius: 27px;

    }

    .visual {

        min-height: 270px;

        padding: 28px;

    }

    .logo-box {

        width: 62px;

        height: 62px;

        border-radius: 19px;

        font-size: 27px;

    }

    .visual-kicker {

        margin-top: 19px;

    }

    .visual-title {

        font-size: 32px;

    }

    .form-side {

        padding:
            30px 24px 28px;

    }

    .title {

        font-size: 29px;

    }

}

@media(prefers-reduced-motion:reduce) {

    *,
    *::before,
    *::after {

        animation-duration:
            .01ms !important;

        animation-iteration-count:
            1 !important;

    }

}

</style>

</head>


<body>


<div class="bg">

    <div class="orb one"></div>

    <div class="orb two"></div>

    <div class="orb three"></div>

    <div class="orb four"></div>

</div>


<main class="login-card">


<section class="visual">


<div class="visual-content">


<div class="logo-box">
    G
</div>


<div class="visual-kicker">
    COLLECTION MANAGEMENT SYSTEM
</div>


<h1 class="visual-title">
    Gulabi Khatoon
</h1>


<p class="visual-description">

    Weekly aur Monthly collection ko
    ek secure, simple aur professional
    dashboard se manage karein.

</p>


<div class="features">


<div class="feature">

    <div class="feature-icon">
        <i class="bi bi-calendar-week"></i>
    </div>

    Weekly collection management

</div>


<div class="feature">

    <div class="feature-icon">
        <i class="bi bi-calendar-month"></i>
    </div>

    Monthly collection management

</div>


<div class="feature">

    <div class="feature-icon">
        <i class="bi bi-graph-up-arrow"></i>
    </div>

    Collection & payment tracking

</div>


<div class="feature">

    <div class="feature-icon">
        <i class="bi bi-shield-lock"></i>
    </div>

    Secure administrator access

</div>


</div>


</div>


</section>


<section class="form-side">


<div class="form-inner">


<div class="kicker">
    ADMIN PORTAL
</div>


<h2 class="title">
    Welcome Back
</h2>


<p class="subtitle">

    Apne account mein login karke
    dashboard continue karein.

</p>


<?php if ($error): ?>

<div class="message error">

    <i class="bi bi-exclamation-circle-fill"></i>

    <span>
        <?= htmlspecialchars($error) ?>
    </span>

</div>

<?php endif; ?>


<?php if ($success): ?>

<div class="message success">

    <i class="bi bi-check-circle-fill"></i>

    <span>
        <?= htmlspecialchars($success) ?>
    </span>

</div>

<?php endif; ?>


<form
    method="post"
    id="loginForm"
    autocomplete="on"
>


<div class="field">


<label class="label">
    USERNAME
</label>


<div class="input-wrap">


<i class="bi bi-person-fill input-icon"></i>


<input
    type="text"
    name="username"
    class="input"
    placeholder="Enter your username"
    autocomplete="username"
    value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
    required
    autofocus
>


</div>


</div>


<div class="field">


<label class="label">
    PASSWORD
</label>


<div class="input-wrap">


<i class="bi bi-lock-fill input-icon"></i>


<input
    type="password"
    name="password"
    id="password"
    class="input"
    placeholder="Enter your password"
    autocomplete="current-password"
    required
>


<button
    type="button"
    class="toggle-password"
    id="togglePassword"
    aria-label="Show password"
>

    <i class="bi bi-eye-fill"></i>

</button>


</div>


</div>


<button
    type="submit"
    class="login-btn"
    id="loginButton"
>

    <span class="spinner"></span>

    <span id="loginText">

        <i class="bi bi-box-arrow-in-right"></i>

        &nbsp;

        Login to Dashboard

    </span>

</button>


</form>


<div class="security">

    <i class="bi bi-shield-check"></i>

    Secure Admin Login

</div>


<div class="footer-text">

    Gulabi Khatoon · Collection Management

</div>


</div>


</section>


</main>


<script>

const password =
    document.getElementById(
        'password'
    );


const togglePassword =
    document.getElementById(
        'togglePassword'
    );


if (
    togglePassword &&
    password
) {

    togglePassword.addEventListener(
        'click',
        function () {

            const visible =
                password.type === 'text';

            password.type =
                visible
                    ? 'password'
                    : 'text';

            this.innerHTML =
                visible
                    ? '<i class="bi bi-eye-fill"></i>'
                    : '<i class="bi bi-eye-slash-fill"></i>';

            this.setAttribute(
                'aria-label',
                visible
                    ? 'Show password'
                    : 'Hide password'
            );

        }
    );

}


const loginForm =
    document.getElementById(
        'loginForm'
    );


const loginButton =
    document.getElementById(
        'loginButton'
    );


const loginText =
    document.getElementById(
        'loginText'
    );


if (loginForm) {

    loginForm.addEventListener(
        'submit',
        function () {

            if (
                loginButton &&
                loginText
            ) {

                loginButton.classList.add(
                    'loading'
                );

                loginText.innerHTML =
                    'Signing in...';

            }

        }
    );

}

</script>


</body>

</html>