<?php

session_start();

if (isset($_SESSION['admin'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $u = trim($_POST['username'] ?? '');
    $p = $_POST['password'] ?? '';

    if ($u === 'admin' && $p === 'admin123') {

        $_SESSION['admin'] = $u;

        header('Location: dashboard.php');
        exit;
    }

    $error = 'Invalid username or password.';
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

<title>AliBrose Bank — Login</title>


<!-- Bootstrap -->

<link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet"
>


<!-- Bootstrap Icons -->

<link
    href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
    rel="stylesheet"
>


<style>

/* =====================================================
   RESET
===================================================== */

*{
    box-sizing:border-box;
}

html,
body{

    width:100%;
    height:100%;

    margin:0;
    padding:0;
}


body{

    overflow:hidden;

    font-family:
        Inter,
        system-ui,
        -apple-system,
        BlinkMacSystemFont,
        "Segoe UI",
        sans-serif;

    background:#020604;

    color:#fff;
}


/* =====================================================
   MAIN LIVE BACKGROUND
===================================================== */

.login-page{

    position:relative;

    width:100%;

    min-height:100vh;

    overflow:hidden;

    display:flex;

    align-items:center;

    justify-content:center;

    background:

        radial-gradient(
            circle at 15% 20%,
            rgba(22,163,74,.20),
            transparent 28%
        ),

        radial-gradient(
            circle at 85% 75%,
            rgba(34,197,94,.13),
            transparent 30%
        ),

        radial-gradient(
            circle at 50% 100%,
            rgba(21,128,61,.15),
            transparent 35%
        ),

        #020604;
}


/* =====================================================
   MOVING GRID
===================================================== */

.grid-bg{

    position:absolute;

    inset:-50%;

    background-image:

        linear-gradient(
            rgba(34,197,94,.045) 1px,
            transparent 1px
        ),

        linear-gradient(
            90deg,
            rgba(34,197,94,.045) 1px,
            transparent 1px
        );

    background-size:55px 55px;

    transform:
        perspective(500px)
        rotateX(60deg)
        translateY(120px)
        scale(1.5);

    animation:
        gridMove 18s linear infinite;

    opacity:.7;
}


@keyframes gridMove{

    from{

        background-position:
            0 0,
            0 0;
    }

    to{

        background-position:
            0 55px,
            55px 0;
    }
}


/* =====================================================
   GLOW ORBS
===================================================== */

.orb{

    position:absolute;

    border-radius:50%;

    pointer-events:none;

}


.orb-1{

    width:420px;

    height:420px;

    left:-170px;

    top:-130px;

    background:
        radial-gradient(
            circle,
            rgba(34,197,94,.25),
            rgba(34,197,94,0) 70%
        );

    animation:
        floatOne 9s ease-in-out infinite;
}


.orb-2{

    width:500px;

    height:500px;

    right:-220px;

    bottom:-220px;

    background:
        radial-gradient(
            circle,
            rgba(22,163,74,.22),
            rgba(22,163,74,0) 70%
        );

    animation:
        floatTwo 11s ease-in-out infinite;
}


.orb-3{

    width:230px;

    height:230px;

    right:15%;

    top:8%;

    background:
        radial-gradient(
            circle,
            rgba(74,222,128,.12),
            transparent 70%
        );

    animation:
        floatThree 7s ease-in-out infinite;
}


@keyframes floatOne{

    0%,100%{
        transform:
            translate(0,0)
            scale(1);
    }

    50%{
        transform:
            translate(80px,50px)
            scale(1.12);
    }
}


@keyframes floatTwo{

    0%,100%{
        transform:
            translate(0,0)
            scale(1);
    }

    50%{
        transform:
            translate(-70px,-50px)
            scale(1.1);
    }
}


@keyframes floatThree{

    0%,100%{
        transform:
            translateY(0);
    }

    50%{
        transform:
            translateY(55px);
    }
}


/* =====================================================
   PARTICLES
===================================================== */

.particles{

    position:absolute;

    inset:0;

    overflow:hidden;

    pointer-events:none;
}


.particles span{

    position:absolute;

    width:3px;

    height:3px;

    border-radius:50%;

    background:#4ade80;

    box-shadow:
        0 0 10px
        rgba(74,222,128,.9);

    opacity:.55;

    animation:
        particleFloat linear infinite;
}


.particles span:nth-child(1){
    left:8%;
    top:80%;
    animation-duration:11s;
    animation-delay:-3s;
}

.particles span:nth-child(2){
    left:17%;
    top:35%;
    animation-duration:14s;
    animation-delay:-7s;
}

.particles span:nth-child(3){
    left:29%;
    top:75%;
    animation-duration:10s;
    animation-delay:-2s;
}

.particles span:nth-child(4){
    left:42%;
    top:25%;
    animation-duration:15s;
    animation-delay:-8s;
}

.particles span:nth-child(5){
    left:54%;
    top:82%;
    animation-duration:12s;
    animation-delay:-5s;
}

.particles span:nth-child(6){
    left:65%;
    top:30%;
    animation-duration:13s;
    animation-delay:-4s;
}

.particles span:nth-child(7){
    left:74%;
    top:72%;
    animation-duration:10s;
    animation-delay:-6s;
}

.particles span:nth-child(8){
    left:83%;
    top:42%;
    animation-duration:16s;
    animation-delay:-10s;
}

.particles span:nth-child(9){
    left:92%;
    top:78%;
    animation-duration:12s;
    animation-delay:-4s;
}

.particles span:nth-child(10){
    left:48%;
    top:55%;
    animation-duration:17s;
    animation-delay:-9s;
}


@keyframes particleFloat{

    0%{

        transform:
            translateY(80px)
            scale(.6);

        opacity:0;
    }

    20%{
        opacity:.6;
    }

    80%{
        opacity:.6;
    }

    100%{

        transform:
            translateY(-300px)
            scale(1.2);

        opacity:0;
    }
}


/* =====================================================
   LOGIN WRAPPER
===================================================== */

.login-wrapper{

    position:relative;

    z-index:10;

    width:min(
        1050px,
        calc(100% - 40px)
    );

    display:grid;

    grid-template-columns:
        1fr 430px;

    gap:50px;

    align-items:center;
}


/* =====================================================
   LEFT BRAND
===================================================== */

.brand-area{

    padding:20px;
}


.brand-icon{

    width:72px;

    height:72px;

    display:flex;

    align-items:center;

    justify-content:center;

    border-radius:22px;

    background:
        linear-gradient(
            135deg,
            #16a34a,
            #22c55e
        );

    color:#fff;

    font-size:31px;

    box-shadow:

        0 0 0 8px
        rgba(34,197,94,.06),

        0 20px 50px
        rgba(22,163,74,.25);

    animation:
        iconPulse 3s ease-in-out infinite;
}


@keyframes iconPulse{

    0%,100%{
        transform:scale(1);
    }

    50%{
        transform:scale(1.04);
    }
}


.brand-area h1{

    margin:
        25px 0 10px;

    font-size:
        clamp(
            42px,
            5vw,
            68px
        );

    line-height:1;

    font-weight:900;

    letter-spacing:-3px;
}


.brand-area h1 span{

    color:#22c55e;
}


.brand-area p{

    max-width:480px;

    color:#94a3a0;

    font-size:15px;

    line-height:1.7;
}


/* =====================================================
   FEATURES
===================================================== */

.feature-row{

    display:flex;

    gap:10px;

    flex-wrap:wrap;

    margin-top:28px;
}


.feature{

    display:flex;

    align-items:center;

    gap:8px;

    padding:
        9px 12px;

    border:
        1px solid
        rgba(
            255,
            255,
            255,
            .08
        );

    border-radius:10px;

    background:
        rgba(
            255,
            255,
            255,
            .035
        );

    color:#b7c4bc;

    font-size:11px;
}


.feature i{

    color:#4ade80;
}


/* =====================================================
   LOGIN CARD
===================================================== */

.login-card{

    position:relative;

    padding:32px;

    border-radius:26px;

    background:

        linear-gradient(
            145deg,
            rgba(255,255,255,.12),
            rgba(255,255,255,.055)
        );

    border:
        1px solid
        rgba(
            255,
            255,
            255,
            .14
        );

    backdrop-filter:
        blur(22px);

    -webkit-backdrop-filter:
        blur(22px);

    box-shadow:

        0 30px 90px
        rgba(
            0,
            0,
            0,
            .45
        ),

        inset 0 1px 0
        rgba(
            255,
            255,
            255,
            .08
        );
}


.login-card::before{

    content:"";

    position:absolute;

    width:180px;

    height:180px;

    right:-100px;

    top:-100px;

    border-radius:50%;

    background:
        rgba(
            34,
            197,
            94,
            .13
        );

    filter:blur(20px);

    pointer-events:none;
}


/* =====================================================
   LOGIN LOGO
===================================================== */

.login-logo{

    width:58px;

    height:58px;

    display:flex;

    align-items:center;

    justify-content:center;

    border-radius:17px;

    background:
        rgba(
            34,
            197,
            94,
            .12
        );

    border:
        1px solid
        rgba(
            74,
            222,
            128,
            .18
        );

    color:#4ade80;

    font-size:24px;

    margin-bottom:20px;
}


/* =====================================================
   LOGIN TEXT
===================================================== */

.login-card h2{

    font-size:25px;

    font-weight:800;

    margin:0;
}


.login-card .subtitle{

    color:#87958d;

    font-size:12px;

    margin:
        6px 0 25px;
}


/* =====================================================
   ERROR
===================================================== */

.error-box{

    display:flex;

    align-items:center;

    gap:9px;

    padding:11px 12px;

    border-radius:11px;

    background:
        rgba(
            239,
            68,
            68,
            .10
        );

    border:
        1px solid
        rgba(
            248,
            113,
            113,
            .18
        );

    color:#fca5a5;

    font-size:11px;

    margin-bottom:17px;
}


.error-box i{

    color:#f87171;
}


/* =====================================================
   FORM LABEL
===================================================== */

.form-label{

    color:#b7c4bc;

    font-size:11px;

    font-weight:700;

    margin-bottom:7px;
}


/* =====================================================
   INPUT
===================================================== */

.input-box{

    position:relative;

    margin-bottom:17px;
}


.input-box > i{

    position:absolute;

    left:15px;

    top:50%;

    transform:
        translateY(-50%);

    color:#64756b;

    font-size:15px;

    z-index:2;
}


.input-box input{

    width:100%;

    height:49px;

    padding:
        0 45px 0 43px;

    border:
        1px solid
        rgba(
            255,
            255,
            255,
            .10
        );

    border-radius:12px;

    outline:none;

    background:
        rgba(
            0,
            0,
            0,
            .18
        );

    color:#fff;

    font-size:13px;

    transition:.2s;
}


.input-box input::placeholder{

    color:#64756b;
}


.input-box input:focus{

    border-color:#22c55e;

    background:
        rgba(
            0,
            0,
            0,
            .25
        );

    box-shadow:
        0 0 0 4px
        rgba(
            34,
            197,
            94,
            .08
        );
}


.input-box:focus-within > i{

    color:#4ade80;
}


/* =====================================================
   PASSWORD BUTTON
===================================================== */

.password-toggle{

    position:absolute;

    right:13px;

    top:50%;

    transform:
        translateY(-50%);

    border:0;

    background:none;

    color:#64756b;

    cursor:pointer;

    z-index:3;
}


.password-toggle:hover{

    color:#4ade80;
}


/* =====================================================
   LOGIN BUTTON
===================================================== */

.login-btn{

    width:100%;

    height:50px;

    border:0;

    border-radius:12px;

    background:
        linear-gradient(
            135deg,
            #15803d,
            #22c55e
        );

    color:#fff;

    font-size:13px;

    font-weight:800;

    cursor:pointer;

    display:flex;

    align-items:center;

    justify-content:center;

    gap:8px;

    box-shadow:
        0 12px 28px
        rgba(
            22,
            163,
            74,
            .20
        );

    transition:.2s;
}


.login-btn:hover{

    transform:
        translateY(-2px);

    box-shadow:
        0 16px 35px
        rgba(
            22,
            163,
            74,
            .28
        );
}


.login-btn:disabled{

    cursor:not-allowed;

    opacity:.85;

}


/* =====================================================
   BUTTON SPINNER
===================================================== */

.spin{

    display:inline-block;

    animation:
        spin .8s linear infinite;
}


@keyframes spin{

    to{
        transform:rotate(360deg);
    }
}


/* =====================================================
   SECURITY
===================================================== */

.security-text{

    display:flex;

    align-items:center;

    justify-content:center;

    gap:6px;

    color:#64756b;

    font-size:10px;

    margin-top:18px;
}


.security-text i{

    color:#4ade80;
}


/* =====================================================
   DEMO
===================================================== */

.demo-login{

    margin-top:15px;

    padding:10px;

    border-radius:10px;

    text-align:center;

    color:#65746b;

    background:
        rgba(
            255,
            255,
            255,
            .035
        );

    border:
        1px solid
        rgba(
            255,
            255,
            255,
            .06
        );

    font-size:10px;
}


.demo-login strong{

    color:#9ee6b4;
}


/* =====================================================
   FULL SCREEN LOGIN LOADER
===================================================== */

.login-loader{

    position:fixed;

    inset:0;

    z-index:99999;

    display:none;

    align-items:center;

    justify-content:center;

    background:

        radial-gradient(
            circle at 50% 40%,
            rgba(22,163,74,.18),
            transparent 30%
        ),

        #020604;

    backdrop-filter:
        blur(12px);
}


.login-loader.show{

    display:flex;
}


.loader-content{

    width:340px;

    max-width:
        calc(
            100% - 40px
        );

    text-align:center;
}


.loader-logo{

    width:70px;

    height:70px;

    margin:auto;

    display:flex;

    align-items:center;

    justify-content:center;

    border-radius:20px;

    background:
        linear-gradient(
            135deg,
            #16a34a,
            #22c55e
        );

    color:#fff;

    font-size:29px;

    box-shadow:
        0 0 45px
        rgba(
            34,
            197,
            94,
            .25
        );

    animation:
        loaderPulse 1.5s infinite;
}


@keyframes loaderPulse{

    0%,100%{
        transform:scale(1);
    }

    50%{
        transform:scale(1.08);
    }
}


.loader-content h3{

    margin:
        18px 0 4px;

    color:#fff;

    font-size:24px;

    font-weight:800;
}


.loader-content h3 span{

    color:#22c55e;
}


.loader-content p{

    margin:
        0 0 28px;

    color:#718078;

    font-size:12px;
}


/* =====================================================
   LOADER CIRCLE
===================================================== */

.loader-circle{

    width:90px;

    height:90px;

    margin:auto;

    position:relative;

    display:flex;

    align-items:center;

    justify-content:center;

    border-radius:50%;
}


.loader-circle > i{

    color:#4ade80;

    font-size:22px;

    position:relative;

    z-index:2;
}


.loader-ring{

    position:absolute;

    inset:0;

    border-radius:50%;

    border:
        3px solid
        rgba(
            34,
            197,
            94,
            .12
        );

    border-top-color:#22c55e;

    border-right-color:#16a34a;

    animation:
        loaderRotate 1s linear infinite;
}


@keyframes loaderRotate{

    to{
        transform:rotate(360deg);
    }
}


/* =====================================================
   PROGRESS
===================================================== */

.loader-progress{

    width:100%;

    height:5px;

    margin-top:30px;

    border-radius:20px;

    background:
        rgba(
            255,
            255,
            255,
            .08
        );

    overflow:hidden;
}


.loader-progress-bar{

    width:0%;

    height:100%;

    border-radius:20px;

    background:
        linear-gradient(
            90deg,
            #15803d,
            #22c55e,
            #4ade80
        );

    transition:
        width .1s linear;

    box-shadow:
        0 0 12px
        rgba(
            34,
            197,
            94,
            .5
        );
}


.loader-percent{

    margin-top:10px;

    color:#64756b;

    font-size:11px;
}


/* =====================================================
   RESPONSIVE
===================================================== */

@media(max-width:850px){

    body{
        overflow:auto;
    }


    .login-page{

        min-height:100vh;

        padding:
            35px 0;
    }


    .login-wrapper{

        grid-template-columns:1fr;

        width:min(
            500px,
            calc(100% - 30px)
        );

        gap:25px;
    }


    .brand-area{

        text-align:center;

        padding:0;
    }


    .brand-area h1{

        font-size:43px;

        letter-spacing:-2px;
    }


    .brand-area p{

        margin:
            12px auto 0;
    }


    .feature-row{

        justify-content:center;
    }

}


@media(max-width:500px){

    .login-card{

        padding:24px;

        border-radius:21px;
    }


    .brand-icon{

        width:60px;

        height:60px;

        font-size:25px;
    }


    .brand-area h1{

        margin-top:17px;

        font-size:37px;
    }


    .brand-area p{

        font-size:12px;
    }


    .feature{

        font-size:10px;
    }

}

</style>

</head>


<body>


<div class="login-page">


    <!-- =================================================
         LIVE BACKGROUND
    ================================================== -->

    <div class="grid-bg"></div>

    <div class="orb orb-1"></div>

    <div class="orb orb-2"></div>

    <div class="orb orb-3"></div>


    <!-- PARTICLES -->

    <div class="particles">

        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>

    </div>


    <!-- =================================================
         LOGIN CONTENT
    ================================================== -->

    <div class="login-wrapper">


        <!-- =================================================
             LEFT BRAND
        ================================================== -->

        <div class="brand-area">


            <div class="brand-icon">

                <i class="bi bi-bank2"></i>

            </div>


            <h1>

                Ali<span>Brose</span>

            </h1>


            <p>

                A modern banking management platform
                designed to manage customers, payment
                plans and collections from one secure
                dashboard.

            </p>


            <div class="feature-row">


                <div class="feature">

                    <i class="bi bi-shield-check"></i>

                    Secure Access

                </div>


                <div class="feature">

                    <i class="bi bi-graph-up-arrow"></i>

                    Smart Management

                </div>


                <div class="feature">

                    <i class="bi bi-lightning-charge"></i>

                    Fast & Simple

                </div>

            </div>


        </div>


        <!-- =================================================
             LOGIN CARD
        ================================================== -->

        <div class="login-card">


            <div class="login-logo">

                <i class="bi bi-person-lock"></i>

            </div>


            <h2>

                Welcome Back

            </h2>


            <p class="subtitle">

                Sign in to access your banking dashboard.

            </p>


            <!-- ERROR -->

            <?php if ($error): ?>

                <div class="error-box">

                    <i
                        class="bi
                        bi-exclamation-circle-fill"
                    ></i>

                    <span>

                        <?= htmlspecialchars($error) ?>

                    </span>

                </div>

            <?php endif; ?>


            <!-- =================================================
                 FORM
            ================================================== -->

            <form
                method="post"
                id="loginForm"
            >


                <!-- USERNAME -->

                <label class="form-label">

                    Username

                </label>


                <div class="input-box">

                    <i class="bi bi-person"></i>


                    <input
                        type="text"
                        name="username"
                        placeholder="Enter your username"
                        autocomplete="username"
                        required
                    >

                </div>


                <!-- PASSWORD -->

                <label class="form-label">

                    Password

                </label>


                <div class="input-box">

                    <i class="bi bi-lock"></i>


                    <input
                        type="password"
                        name="password"
                        id="password"
                        placeholder="Enter your password"
                        autocomplete="current-password"
                        required
                    >


                    <button
                        type="button"
                        class="password-toggle"
                        onclick="togglePassword()"
                    >

                        <i
                            class="bi bi-eye"
                            id="eyeIcon"
                        ></i>

                    </button>

                </div>


                <!-- LOGIN BUTTON -->

                <button
                    type="submit"
                    class="login-btn"
                    id="loginBtn"
                >

                    <span id="loginBtnText">

                        Sign In

                        <i
                            class="bi bi-arrow-right"
                        ></i>

                    </span>


                    <span
                        id="loginBtnLoader"
                        style="display:none;"
                    >

                        <i
                            class="bi bi-arrow-repeat spin"
                        ></i>

                        Signing In...

                    </span>

                </button>


            </form>


            <!-- SECURITY -->

            <div class="security-text">

                <i
                    class="bi bi-shield-lock-fill"
                ></i>

                Protected administrator login

            </div>


            <!-- DEMO -->

            <div class="demo-login">

                Demo:

                <strong>admin</strong>

                /

                <strong>admin123</strong>

            </div>


        </div>


    </div>

</div>


<!-- =====================================================
     FULL SCREEN LOADER
===================================================== -->

<div
    id="loginLoader"
    class="login-loader"
>


    <div class="loader-content">


        <div class="loader-logo">

            <i class="bi bi-bank2"></i>

        </div>


        <h3>

            Ali<span>Brose</span>

        </h3>


        <p>

            Securely signing you in...

        </p>


        <div class="loader-circle">

            <div class="loader-ring"></div>

            <i
                class="bi bi-shield-lock-fill"
            ></i>

        </div>


        <div class="loader-progress">

            <div
                id="loaderProgress"
                class="loader-progress-bar"
            ></div>

        </div>


        <div
            id="loaderPercent"
            class="loader-percent"
        >

            0%

        </div>


    </div>


</div>


<script>

/* =====================================================
   PASSWORD SHOW / HIDE
===================================================== */

function togglePassword(){

    const input =
        document.getElementById(
            'password'
        );

    const icon =
        document.getElementById(
            'eyeIcon'
        );


    if(
        input.type === 'password'
    ){

        input.type = 'text';

        icon.className =
            'bi bi-eye-slash';

    }
    else{

        input.type = 'password';

        icon.className =
            'bi bi-eye';

    }

}


/* =====================================================
   LOGIN 5 SECOND LOADER
===================================================== */

const loginForm =
    document.getElementById(
        'loginForm'
    );


const loginBtn =
    document.getElementById(
        'loginBtn'
    );


const loginBtnText =
    document.getElementById(
        'loginBtnText'
    );


const loginBtnLoader =
    document.getElementById(
        'loginBtnLoader'
    );


const loginLoader =
    document.getElementById(
        'loginLoader'
    );


const loaderProgress =
    document.getElementById(
        'loaderProgress'
    );


const loaderPercent =
    document.getElementById(
        'loaderPercent'
    );


loginForm.addEventListener(
    'submit',
    function(event){

        /*
         * Normal submit ko temporarily
         * rok rahe hain.
         */

        event.preventDefault();


        /* Button disable */

        loginBtn.disabled = true;


        /* Button text */

        loginBtnText.style.display =
            'none';


        /* Button loader */

        loginBtnLoader.style.display =
            'inline-flex';


        /* Full screen loader */

        loginLoader.classList.add(
            'show'
        );


        let progress = 0;


        /*
         * 100 steps × 50ms
         * = 5000ms = 5 seconds
         */

        const interval =
            setInterval(
                function(){

                    progress += 1;


                    loaderProgress.style.width =
                        progress + '%';


                    loaderPercent.textContent =
                        progress + '%';


                    /*
                     * 100% complete
                     */

                    if(
                        progress >= 100
                    ){

                        clearInterval(
                            interval
                        );


                        /*
                         * Ab actual PHP
                         * form submit hoga.
                         */

                        loginForm.submit();

                    }

                },
                50
            );

    }
);

</script>


</body>

</html>