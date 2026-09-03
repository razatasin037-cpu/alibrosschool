<?php

require_once __DIR__ . '/../config/db.php';

$page_title = $page_title ?? 'AliBrose Bank';

$currentPage = basename(
    $_SERVER['PHP_SELF']
);

?>

<!doctype html>

<html lang="en">

<head>

<meta charset="utf-8">

<meta
    name="viewport"
    content="width=device-width,initial-scale=1"
>

<title>
    <?= htmlspecialchars($page_title) ?>
</title>


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

:root{

    --black:#070b09;

    --black2:#050806;

    --green:#16a34a;

    --green2:#22c55e;

    --green-light:#eaf8ef;

    --bg:#f5f7f6;

    --text:#101713;

    --muted:#6b756e;

}


/* =====================================================
   RESET
===================================================== */

*{
    box-sizing:border-box;
}


html,
body{
    margin:0;
    padding:0;
}


body{

    background:var(--bg);

    color:var(--text);

    font-family:
        Inter,
        system-ui,
        -apple-system,
        "Segoe UI",
        sans-serif;

    overflow-x:hidden;
}


/* =====================================================
   SIDEBAR
===================================================== */

.sidebar{

    position:fixed;

    left:0;
    top:0;

    width:260px;

    height:100vh;

    background:
        linear-gradient(
            180deg,
            #050806 0%,
            #0b110d 100%
        );

    border-right:
        1px solid #172019;

    z-index:1050;

    display:flex;

    flex-direction:column;

    overflow-y:auto;

    overflow-x:hidden;

    scrollbar-width:thin;

    scrollbar-color:#166534 #050806;
}


/* Chrome scrollbar */

.sidebar::-webkit-scrollbar{
    width:5px;
}


.sidebar::-webkit-scrollbar-track{
    background:#050806;
}


.sidebar::-webkit-scrollbar-thumb{

    background:#166534;

    border-radius:10px;
}


/* =====================================================
   BRAND
===================================================== */

.brand{

    min-height:76px;

    display:flex;

    align-items:center;

    gap:10px;

    font-size:23px;

    font-weight:800;

    color:#fff;

    padding:
        18px 20px;

    border-bottom:
        1px solid rgba(
            255,
            255,
            255,
            .06
        );

    flex-shrink:0;
}


.brand i{

    width:40px;

    height:40px;

    display:flex;

    align-items:center;

    justify-content:center;

    background:
        linear-gradient(
            135deg,
            #16a34a,
            #22c55e
        );

    border-radius:12px;

    color:#fff;

    font-size:19px;

    box-shadow:
        0 8px 20px
        rgba(
            34,
            197,
            94,
            .18
        );
}


.brand span{
    color:var(--green2);
}


/* =====================================================
   MENU
===================================================== */

.menu{

    padding:
        18px 14px;

    flex:1;
}


/* =====================================================
   NAV LINKS
===================================================== */

.nav-linkx{

    position:relative;

    display:flex;

    align-items:center;

    gap:12px;

    color:#aeb9b1;

    text-decoration:none;

    padding:
        12px 14px;

    border-radius:12px;

    margin:5px 0;

    transition:
        .2s ease;

    font-size:14px;

    font-weight:500;
}


.nav-linkx i{

    width:22px;

    text-align:center;

    font-size:16px;
}


.nav-linkx:hover{

    background:
        rgba(
            34,
            197,
            94,
            .08
        );

    color:#fff;

    transform:
        translateX(2px);
}


.nav-linkx.active{

    background:
        linear-gradient(
            90deg,
            rgba(
                34,
                197,
                94,
                .16
            ),
            rgba(
                34,
                197,
                94,
                .04
            )
        );

    color:#fff;

    box-shadow:
        inset 3px 0 0
        var(--green2);
}


.nav-linkx.active i{

    color:var(--green2);
}


/* =====================================================
   MAIN
===================================================== */

main{

    margin-left:260px;

    min-height:100vh;

    width:
        calc(
            100% - 260px
        );
}


/* =====================================================
   TOPBAR
===================================================== */

.topbar{

    background:
        rgba(
            255,
            255,
            255,
            .94
        );

    backdrop-filter:
        blur(12px);

    border-bottom:
        1px solid #e5e7eb;

    position:sticky;

    top:0;

    z-index:1000;

    min-height:70px;
}


/* =====================================================
   CONTENT
===================================================== */

.content{

    padding:28px;

    min-height:
        calc(
            100vh - 70px
        );
}


/* =====================================================
   CARDS
===================================================== */

.cardx{

    background:#fff;

    border:
        1px solid #e8ece9;

    border-radius:20px;

    box-shadow:
        0 10px 28px
        rgba(
            9,
            18,
            12,
            .055
        );
}


/* =====================================================
   STAT CARD
===================================================== */

.stat{

    position:relative;

    overflow:hidden;
}


.stat:after{

    content:"";

    position:absolute;

    width:105px;

    height:105px;

    border-radius:50%;

    right:-38px;

    top:-42px;

    background:#e9f8ee;

    pointer-events:none;
}


/* =====================================================
   ICON
===================================================== */

.iconbox{

    width:52px;

    height:52px;

    border-radius:15px;

    display:flex;

    align-items:center;

    justify-content:center;

    background:
        var(--green-light);

    color:
        var(--green);

    font-size:23px;

    position:relative;

    z-index:1;
}


/* =====================================================
   TEXT
===================================================== */

.amount{

    font-size:25px;

    font-weight:800;
}


.muted{

    color:var(--muted);
}


/* =====================================================
   TABLE
===================================================== */

.table{

    --bs-table-bg:transparent;

    vertical-align:middle;
}


.table thead th{

    color:#6b756e;

    font-size:13px;

    font-weight:700;

    border-bottom:
        1px solid #edf0ee;

    white-space:nowrap;
}


.table td{

    padding:
        15px 10px;

    border-color:#edf0ee;
}


/* =====================================================
   AVATAR
===================================================== */

.avatar{

    width:38px;

    height:38px;

    border-radius:50%;

    display:inline-flex;

    align-items:center;

    justify-content:center;

    background:#dcfce7;

    color:#15803d;

    font-weight:800;

    flex-shrink:0;
}


/* =====================================================
   HERO
===================================================== */

.hero{

    background:
        linear-gradient(
            135deg,
            #050806,
            #102318 58%,
            #166534
        );

    color:#fff;

    border-radius:22px;

    padding:28px;

    overflow:hidden;

    position:relative;
}


.hero:after{

    content:"";

    position:absolute;

    width:260px;

    height:260px;

    border-radius:50%;

    right:-80px;

    top:-115px;

    background:
        rgba(
            34,
            197,
            94,
            .12
        );

    pointer-events:none;
}


/* =====================================================
   BUTTONS
===================================================== */

.btn-primary,
.btn-success{

    background:
        var(--green);

    border-color:
        var(--green);
}


.btn-primary:hover,
.btn-success:hover{

    background:#15803d;

    border-color:#15803d;
}


/* =====================================================
   FORMS
===================================================== */

.form-control,
.form-select{

    border-radius:11px;

    padding:
        11px 13px;

    border-color:#dfe5e1;
}


.form-control:focus,
.form-select:focus{

    border-color:#86efac;

    box-shadow:
        0 0 0 .2rem
        rgba(
            34,
            197,
            94,
            .13
        );
}


/* =====================================================
   PROGRESS
===================================================== */

.progress-bar{

    background:
        var(--green);
}


/* =====================================================
   MOBILE BUTTON
===================================================== */

.mobile-menu-btn{

    width:42px;

    height:42px;

    border:0;

    border-radius:11px;

    display:none;

    align-items:center;

    justify-content:center;

    background:#020b07;

    color:#fff;

    font-size:20px;
}


/* =====================================================
   SIDEBAR OVERLAY
===================================================== */

.sidebar-overlay{

    display:none;

    position:fixed;

    inset:0;

    background:
        rgba(
            0,
            0,
            0,
            .20
        );

    z-index:1040;
}


/* =====================================================
   MOBILE
===================================================== */

@media(max-width:991px){

    .sidebar{

        width:260px;

        transform:
            translateX(-100%);

        transition:
            transform .25s ease;

        box-shadow:none;
    }


    .sidebar.show{

        transform:
            translateX(0);
    }


    main{

        margin-left:0;

        width:100%;
    }


    .content{

        padding:18px;
    }


    .mobile-menu-btn{

        display:flex;
    }


    .sidebar-overlay.show{

        display:block;
    }

}


/* =====================================================
   SMALL MOBILE
===================================================== */

@media(max-width:575px){

    .content{

        padding:14px;
    }


    .topbar{

        padding:
            12px 14px !important;
    }


    .hero{

        padding:22px;

        border-radius:18px;
    }

}


/* =====================================================
   PRINT
===================================================== */

@media print{

    .sidebar,
    .topbar,
    .no-print{

        display:none!important;
    }


    main{

        margin-left:0;

        width:100%;
    }


    .content{

        padding:0;
    }


    .cardx{

        box-shadow:none;

        border:0;
    }

}

</style>

</head>


<body>


<!-- =====================================================
     SIDEBAR OVERLAY
===================================================== -->

<div
    class="sidebar-overlay"
    id="sidebarOverlay"
    onclick="closeSidebar()"
></div>


<!-- =====================================================
     SIDEBAR
===================================================== -->

<aside
    class="sidebar"
    id="mainSidebar"
>


    <!-- BRAND -->

    <div class="brand">

        <i class="bi bi-bank2"></i>

        <div>

            Ali<span>Brose</span>

            <div
                style="
                    font-size:9px;
                    color:#64748b;
                    font-weight:500;
                    margin-top:3px;
                "
            >

                BANK MANAGEMENT

            </div>

        </div>

    </div>


    <!-- MENU -->

    <div class="menu">


        <!-- DASHBOARD -->

        <a
            class="nav-linkx
            <?= $currentPage === 'dashboard.php'
                ? 'active'
                : '' ?>"
            href="dashboard.php"
        >

            <i
                class="bi
                bi-grid-1x2-fill"
            ></i>

            <span>
                Dashboard
            </span>

        </a>


        <!-- CUSTOMERS -->

        <a
            class="nav-linkx
            <?= $currentPage === 'customers.php'
                ? 'active'
                : '' ?>"
            href="customers.php"
        >

            <i
                class="bi
                bi-people-fill"
            ></i>

            <span>
                Customers
            </span>

        </a>



        <!-- COLLECTION -->

        <a
            class="nav-linkx
            <?= $currentPage === 'collection_details.php'
                ? 'active'
                : '' ?>"
            href="collection_details.php"
        >

            <i
                class="bi
                bi-bar-chart-fill"
            ></i>

            <span>
                Collection
            </span>

        </a>



        <!-- ADD CUSTOMER -->

        <a
            class="nav-linkx
            <?= $currentPage === 'add_customer.php'
                ? 'active'
                : '' ?>"
            href="add_customer.php"
        >

            <i
                class="bi
                bi-person-plus-fill"
            ></i>

            <span>
                Add Customer
            </span>

        </a>


        <!-- SETTINGS -->

        <?php if (
            file_exists(
                __DIR__ .
                '/../settings.php'
            )
        ): ?>

        <a
            class="nav-linkx
            <?= $currentPage === 'settings.php'
                ? 'active'
                : '' ?>"
            href="settings.php"
        >

            <i
                class="bi
                bi-gear-fill"
            ></i>

            <span>
                Settings
            </span>

        </a>

        <?php endif; ?>


    </div>


    <!-- SIDEBAR BOTTOM -->

    <div
        style="
            padding:14px;
            border-top:
                1px solid
                rgba(255,255,255,.06);
        "
    >


        <!-- ADMIN -->

        <div
            style="
                display:flex;
                align-items:center;
                gap:10px;
                padding:10px;
                border-radius:12px;
                background:
                    rgba(255,255,255,.04);
                margin-bottom:8px;
            "
        >

            <span
                class="avatar"
                style="
                    background:#16a34a;
                    color:#fff;
                "
            >

                <i
                    class="bi
                    bi-person-fill"
                ></i>

            </span>


            <div>

                <div
                    style="
                        color:#fff;
                        font-size:13px;
                        font-weight:700;
                    "
                >

                    Admin

                </div>


                <div
                    style="
                        color:#64748b;
                        font-size:10px;
                    "
                >

                    Administrator

                </div>

            </div>

        </div>


        <!-- LOGOUT -->

        <a
            class="nav-linkx"
            href="logout.php"
        >

            <i
                class="bi
                bi-box-arrow-right"
            ></i>

            <span>
                Logout
            </span>

        </a>

    </div>


</aside>


<!-- =====================================================
     MAIN
===================================================== -->

<main>


    <!-- TOPBAR -->

    <header
        class="topbar
        px-4
        py-3
        d-flex
        justify-content-between
        align-items-center"
    >


        <div
            class="d-flex
            align-items-center
            gap-3"
        >


            <!-- MOBILE MENU -->

            <button
                type="button"
                class="mobile-menu-btn"
                onclick="toggleSidebar()"
            >

                <i
                    class="bi
                    bi-list"
                ></i>

            </button>


            <div>

                <div
                    class="small muted"
                >

                    Bank Administration

                </div>


                <strong>

                    <?= htmlspecialchars(
                        $page_title
                    ) ?>

                </strong>

            </div>

        </div>


        <!-- ADMIN -->

        <div
            class="d-flex
            align-items-center
            gap-2"
        >

            <span
                class="avatar"
            >

                <i
                    class="bi
                    bi-person-fill"
                ></i>

            </span>


            <span
                class="d-none
                d-md-inline"
            >

                Admin

            </span>

        </div>

    </header>


    <!-- CONTENT -->

    <section class="content">
<script>
function toggleSidebar() {
    const sidebar = document.getElementById('mainSidebar');
    const overlay = document.getElementById('sidebarOverlay');
    if (!sidebar || !overlay) return;
    sidebar.classList.toggle('show');
    overlay.classList.toggle('show');
}

function closeSidebar() {
    const sidebar = document.getElementById('mainSidebar');
    const overlay = document.getElementById('sidebarOverlay');
    if (!sidebar || !overlay) return;
    sidebar.classList.remove('show');
    overlay.classList.remove('show');
}

document.addEventListener('DOMContentLoaded', function () {
    const sidebar = document.getElementById('mainSidebar');
    const overlay = document.getElementById('sidebarOverlay');
    if (!sidebar || !overlay) return;

    document.querySelectorAll('.nav-linkx').forEach(function (link) {
        link.addEventListener('click', function () {
            if (window.innerWidth <= 991) closeSidebar();
        });
    });

    overlay.addEventListener('click', closeSidebar);

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') closeSidebar();
    });

    window.addEventListener('resize', function () {
        if (window.innerWidth > 991) closeSidebar();
    });
});
</script>