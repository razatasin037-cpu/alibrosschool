<?php

if (!isset($page_title)) {
    $page_title = 'Dashboard';
}

$base_url = '/gulabi_alibrose_full';

$current_page = basename($_SERVER['PHP_SELF']);

$admin_name = $_SESSION['admin_name'] ?? 'Admin';

$current_plan = $_GET['plan'] ?? '';

$is_dashboard = $current_page === 'dashboard.php';
$is_customers = $current_page === 'customers.php';
$is_accounts = $current_page === 'accounts.php';
$is_weekly = $current_page === 'collection_details.php' && $current_plan === 'Weekly';
$is_monthly = $current_page === 'collection_details.php' && $current_plan === 'Monthly';

?>

<!doctype html>

<html lang="en">

<head>

<meta charset="utf-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1"
>

<title>
    <?= htmlspecialchars($page_title) ?> | Gulabi Alibrose
</title>


<link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
>


<link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
>


<link
    rel="stylesheet"
    href="<?= $base_url ?>/assets/css/style.css"
>


<style>

body {
    font-family:
        Arial,
        Helvetica,
        sans-serif;
}


/* =========================
   LOADER
========================= */

.loader {
    position: fixed;
    inset: 0;
    z-index: 99999;

    display: flex;
    align-items: center;
    justify-content: center;

    flex-direction: column;

    background: #fff7fb;

    transition:
        opacity .45s ease,
        visibility .45s ease;
}

.loader.hide {
    opacity: 0;
    visibility: hidden;
    pointer-events: none;
}

.loader-flower {
    width: 64px;
    height: 64px;

    border-radius: 20px;

    display: grid;
    place-items: center;

    background:
        linear-gradient(
            135deg,
            #681033,
            #d81b72,
            #f04a9b
        );

    color: white;

    font-size: 29px;
    font-weight: 900;

    box-shadow:
        0 14px 35px
        rgba(216,27,114,.25);

    animation:
        loaderPulse 1.4s ease-in-out infinite;
}

.loader-text {
    margin-top: 12px;

    color: #7b123f;

    font-weight: 900;

    letter-spacing: .3px;
}

@keyframes loaderPulse {

    0%,
    100% {
        transform:
            scale(1)
            rotate(0deg);
    }

    50% {
        transform:
            scale(1.08)
            rotate(4deg);
    }
}


/* =========================
   TOPBAR
========================= */

.topbar {

    position: fixed;

    top: 0;
    left: 0;
    right: 0;

    height: 72px;

    z-index: 1000;

    display: flex;

    align-items: center;

    justify-content: space-between;

    padding:
        0 25px 0 25px;

    background:
        rgba(255,255,255,.94);

    border-bottom:
        1px solid #edf0ee;

    backdrop-filter:
        blur(18px);

    box-shadow:
        0 5px 25px
        rgba(0,0,0,.035);
}


/* =========================
   BRAND
========================= */

.brand {

    display: flex;

    align-items: center;

    gap: 11px;
}

.brand-logo {

    width: 44px;
    height: 44px;

    border-radius: 14px;

    display: flex;

    align-items: center;
    justify-content: center;

    color: white;

    font-size: 20px;

    font-weight: 900;

    background:
        linear-gradient(
            135deg,
            #681033,
            #d81b72,
            #f04a9b
        );

    box-shadow:
        0 8px 20px
        rgba(216,27,114,.22);

    transition:
        .4s ease;
}

.brand:hover .brand-logo {

    transform:
        rotate(-6deg)
        scale(1.05);
}

.brand strong {

    display: block;

    color: #681033;

    font-size: 14px;

    font-weight: 900;

    line-height: 1.2;
}

.brand small {

    display: block;

    color: #89938d;

    font-size: 9px;

    margin-top: 2px;

    letter-spacing: .2px;
}


/* =========================
   MOBILE MENU
========================= */

.mobile-menu {

    width: 43px;
    height: 43px;

    border: 0;

    border-radius: 13px;

    display: none;

    align-items: center;
    justify-content: center;

    color: #d81b72;

    background: #fff0f7;

    font-size: 22px;

    transition: .35s ease;
}

.mobile-menu:hover {

    color: #fff;

    background: #d81b72;

    transform:
        scale(1.05);
}


/* =========================
   SIDEBAR
========================= */

.sidebar {

    position: fixed;

    top: 72px;
    left: 0;
    bottom: 0;

    width: 255px;

    z-index: 900;

    display: flex;

    flex-direction: column;

    padding:
        25px 15px 16px;

    background:
        linear-gradient(
            180deg,
            #fff,
            #fff9fc
        );

    border-right:
        1px solid #edf0ee;

    box-shadow:
        8px 0 30px
        rgba(0,0,0,.025);

    overflow-y: auto;

    scrollbar-width: thin;

    scrollbar-color:
        #e7b0c8
        transparent;
}


/* =========================
   SIDE TITLE
========================= */

.side-title {

    padding:
        0 12px;

    margin:
        5px 0 10px;

    color: #9aa29d;

    font-size: 9px;

    font-weight: 900;

    letter-spacing:
        1.2px;
}


/* =========================
   SIDEBAR LINKS
========================= */

.sidebar > a {

    position: relative;

    display: flex;

    align-items: center;

    gap: 12px;

    min-height: 45px;

    margin:
        3px 0;

    padding:
        11px 13px;

    border-radius: 13px;

    color: #58635d;

    text-decoration: none;

    font-size: 11px;

    font-weight: 800;

    transition:
        color .35s ease,
        background .35s ease,
        transform .35s ease,
        box-shadow .35s ease;
}

.sidebar > a i {

    width: 22px;

    text-align: center;

    font-size: 16px;

    transition:
        transform .35s ease;
}

.sidebar > a:hover {

    color: #d81b72;

    background:
        #fff0f7;

    transform:
        translateX(4px);
}

.sidebar > a:hover i {

    transform:
        scale(1.15);
}


/* ACTIVE LINK */

.sidebar > a.active {

    color: #fff;

    background:
        linear-gradient(
            100deg,
            #a81254,
            #d81b72,
            #f04a9b
        );

    box-shadow:
        0 9px 22px
        rgba(216,27,114,.20);
}

.sidebar > a.active i {

    transform:
        scale(1.08);
}


/* =========================
   PINK ACTION
========================= */

.sidebar > a.pink-action {

    color: #d81b72;

    background:
        #fff0f7;

    border:
        1px solid #ffd6e8;
}

.sidebar > a.pink-action:hover {

    color: #fff;

    background:
        linear-gradient(
            100deg,
            #b8145f,
            #d81b72
        );

    border-color:
        #d81b72;

    box-shadow:
        0 10px 24px
        rgba(216,27,114,.18);
}


/* =========================
   ADMIN PROFILE
========================= */

.sidebar-admin {

    display: flex;

    align-items: center;

    gap: 10px;

    margin:
        8px 0 15px;

    padding:
        12px;

    border-radius: 15px;

    background:
        linear-gradient(
            135deg,
            #fff0f7,
            #fff
        );

    border:
        1px solid #ffe0ec;
}

.admin-avatar {

    width: 38px;
    height: 38px;

    flex-shrink: 0;

    border-radius: 12px;

    display: flex;

    align-items: center;
    justify-content: center;

    color: #fff;

    font-size: 14px;

    font-weight: 900;

    background:
        linear-gradient(
            135deg,
            #681033,
            #d81b72
        );
}

.admin-info {

    min-width: 0;
}

.admin-info strong {

    display: block;

    color: #303a34;

    font-size: 11px;

    white-space: nowrap;

    overflow: hidden;

    text-overflow: ellipsis;
}

.admin-info span {

    display: block;

    color: #929b95;

    font-size: 8px;

    margin-top: 2px;
}


/* =========================
   SIDEBAR SPACER
========================= */

.sidebar-spacer {

    flex: 1;
}


/* =========================
   LOGOUT
========================= */

.sidebar-logout {

    margin-top: 18px;

    padding-top: 14px;

    border-top:
        1px solid #edf0ee;
}

.sidebar-logout a {

    position: relative;

    display: flex;

    align-items: center;

    gap: 12px;

    width: 100%;

    min-height: 45px;

    padding:
        11px 13px;

    border-radius: 13px;

    color: #c2185b;

    background:
        #fff3f7;

    border:
        1px solid #ffdce9;

    text-decoration: none;

    font-size: 11px;

    font-weight: 900;

    overflow: hidden;

    transition:
        .4s ease;
}

.sidebar-logout a::before {

    content: "";

    position: absolute;

    left: 0;
    top: 0;
    bottom: 0;

    width: 3px;

    background:
        #d81b72;

    transform:
        scaleY(0);

    transform-origin:
        bottom;

    transition:
        .35s ease;
}

.sidebar-logout a:hover {

    color: #fff;

    background:
        linear-gradient(
            100deg,
            #b8145f,
            #d81b72,
            #f04a9b
        );

    border-color:
        #d81b72;

    transform:
        translateX(4px);

    box-shadow:
        0 10px 25px
        rgba(216,27,114,.20);
}

.sidebar-logout a:hover::before {

    transform:
        scaleY(1);
}

.sidebar-logout a i {

    width: 22px;

    text-align: center;

    font-size: 17px;

    transition:
        .4s ease;
}

.sidebar-logout a:hover i {

    transform:
        translateX(4px)
        rotate(-8deg)
        scale(1.15);
}


/* =========================
   MAIN CONTENT
========================= */

.main-content {

    margin-left: 255px;

    padding:
        100px 28px 35px;

    min-height: 100vh;

    background:
        #fafcfb;
}


/* =========================
   MOBILE
========================= */

@media (max-width: 900px) {

    .topbar {

        padding:
            0 16px;
    }

    .mobile-menu {

        display: flex;
    }

    .sidebar {

        width: 255px;

        transform:
            translateX(-105%);

        transition:
            transform .4s
            cubic-bezier(.22,1,.36,1);

        box-shadow:
            15px 0 40px
            rgba(0,0,0,.12);
    }

    .sidebar.open {

        transform:
            translateX(0);
    }

    .main-content {

        margin-left: 0;

        padding:
            92px 16px 25px;
    }
}


@media (max-width: 480px) {

    .brand strong {

        font-size: 12px;
    }

    .brand small {

        display: none;
    }

    .brand-logo {

        width: 40px;
        height: 40px;

        border-radius: 12px;
    }

    .sidebar {

        width: 280px;
    }

}


/* =========================
   SIDEBAR OVERLAY
========================= */

.sidebar-overlay {

    position: fixed;

    inset: 0;

    z-index: 850;

    background:
        rgba(0,0,0,.30);

    backdrop-filter:
        blur(2px);

    opacity: 0;

    visibility: hidden;

    transition:
        .35s ease;
}

.sidebar-overlay.show {

    opacity: 1;

    visibility: visible;
}

@media (min-width: 901px) {

    .sidebar-overlay {

        display: none;
    }
}

</style>

</head>


<body>


<div
    class="loader"
    id="loader"
>

    <div class="loader-flower">
        G
    </div>

    <div class="loader-text">
        Gulabi Alibrose
    </div>

</div>


<header class="topbar">

    <div class="brand">

        <div class="brand-logo">
            G
        </div>

        <div>

            <strong>
                Gulabi Alibrose
            </strong>

            <small>
                Collection Management
            </small>

        </div>

    </div>


    <button
        class="mobile-menu"
        type="button"
        onclick="toggleSidebar()"
        aria-label="Open menu"
    >

        <i class="bi bi-list"></i>

    </button>

</header>


<div
    class="sidebar-overlay"
    id="sidebarOverlay"
    onclick="closeSidebar()"
></div>


<aside
    class="sidebar"
    id="sidebar"
>


    <div class="sidebar-admin">

        <div class="admin-avatar">

            <?= htmlspecialchars(
                strtoupper(
                    mb_substr(
                        $admin_name,
                        0,
                        1,
                        'UTF-8'
                    )
                )
            ) ?>

        </div>

        <div class="admin-info">

            <strong>
                <?= htmlspecialchars($admin_name) ?>
            </strong>

            <span>
                Administrator
            </span>

        </div>

    </div>


    <div class="side-title">
        MAIN MENU
    </div>


    <a
        href="<?= $base_url ?>/dashboard.php"
        class="<?= $current_page === 'dashboard.php' ? 'active' : '' ?>"
    >

        <i class="bi bi-grid-1x2-fill"></i>

        <span>
            Dashboard
        </span>

    </a>


    <a
        href="<?= $base_url ?>/customers.php"
        class="<?= $current_page === 'customers.php' ? 'active' : '' ?>"
    >

        <i class="bi bi-people-fill"></i>

        <span>
            Customers
        </span>

    </a>


    <a
        href="<?= $base_url ?>/collection/collection_details.php?plan=Weekly"
        class="<?= $is_weekly ? 'active collection-active-pulse' : '' ?>"
    >

        <i class="bi bi-calendar-week"></i>

        <span>
            Weekly Collection
        </span>

    </a>


    <a
        href="<?= $base_url ?>/collection/collection_details.php?plan=Monthly"
        class="<?= $is_monthly ? 'active collection-active-pulse' : '' ?>"
    >

        <i class="bi bi-calendar-month"></i>

        <span>
            Monthly Collection
        </span>

    </a>


    <a
        href="<?= $base_url ?>/accounts/accounts.php"
        class="<?= $current_page === 'accounts.php' ? 'active' : '' ?>"
    >

        <i class="bi bi-bank2"></i>

        <span>
            Accounts
        </span>

    </a>


    <div class="side-title mt-4">
        QUICK ACTIONS
    </div>


    <a
        class="pink-action"
        href="<?= $base_url ?>/customers/add_customer.php"
    >

        <i class="bi bi-person-plus-fill"></i>

        <span>
            Add Customer
        </span>

    </a>


    <a
        class="pink-action"
        href="<?= $base_url ?>/accounts/accounts.php"
    >

        <i class="bi bi-plus-circle-fill"></i>

        <span>
            Add Account
        </span>

    </a>


    <div class="sidebar-spacer"></div>


    <div class="sidebar-logout">

        <a
            href="<?= $base_url ?>/logout.php"
            onclick="return confirm('Kya aap logout karna chahte hain?')"
        >

            <i class="bi bi-box-arrow-right"></i>

            <span>
                Logout
            </span>

        </a>

    </div>


</aside>


<main class="main-content">


<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {

        const loader =
            document.getElementById(
                'loader'
            );

        if (loader) {

            setTimeout(
                function () {

                    loader.classList.add(
                        'hide'
                    );

                },
                350
            );
        }


        const sidebar =
            document.getElementById(
                'sidebar'
            );

        const overlay =
            document.getElementById(
                'sidebarOverlay'
            );


        document
            .querySelectorAll(
                '#sidebar a'
            )
            .forEach(
                function (link) {

                    link.addEventListener(
                        'click',
                        function () {

                            if (
                                window.innerWidth <= 900
                            ) {

                                closeSidebar();

                            }

                        }
                    );

                }
            );

    }
);


function toggleSidebar() {

    const sidebar =
        document.getElementById(
            'sidebar'
        );

    const overlay =
        document.getElementById(
            'sidebarOverlay'
        );

    if (!sidebar) {
        return;
    }

    sidebar.classList.toggle(
        'open'
    );

    if (overlay) {

        overlay.classList.toggle(
            'show'
        );

    }

}


function closeSidebar() {

    const sidebar =
        document.getElementById(
            'sidebar'
        );

    const overlay =
        document.getElementById(
            'sidebarOverlay'
        );

    if (sidebar) {

        sidebar.classList.remove(
            'open'
        );

    }

    if (overlay) {

        overlay.classList.remove(
            'show'
        );

    }

}


document.addEventListener(
    'DOMContentLoaded',
    function () {

        document
            .querySelectorAll('#sidebar a.active')
            .forEach(function (link) {

                link.setAttribute(
                    'aria-current',
                    'page'
                );

                const icon =
                    link.querySelector('i');

                if (icon) {
                    icon.classList.add(
                        'active-icon'
                    );
                }
            });

    }
);


document.addEventListener(
    'keydown',
    function (event) {

        if (
            event.key === 'Escape'
        ) {

            closeSidebar();

        }

    }
);

</script>