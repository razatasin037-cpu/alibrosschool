// ============================
// NAVBAR
// ============================

fetch("./navbar.html")
  .then((res) => {
    if (!res.ok) {
      throw new Error("navbar.html load nahi hua");
    }

    return res.text();
  })
  .then((data) => {
    const navbar = document.getElementById("navbar");

    if (!navbar) {
      throw new Error("Navbar container #navbar nahi mila");
    }

    navbar.innerHTML = data;

    setupMobileMenu();
    setupActiveLinks();
  })
  .catch((err) => {
    console.error("Navbar Error:", err);
  });

// ============================
// FOOTER
// ============================

fetch("./footer.html")
  .then((res) => {
    if (!res.ok) {
      throw new Error("footer.html load nahi hua");
    }

    return res.text();
  })
  .then((data) => {
    const footer = document.getElementById("footer");

    if (!footer) {
      throw new Error("Footer container #footer nahi mila");
    }

    footer.innerHTML = data;
  })
  .catch((err) => {
    console.error("Footer Error:", err);
  });

// ============================
// MOBILE MENU
// ============================

function setupMobileMenu() {
  const menuBtn = document.getElementById("menuBtn");
  const mobileMenu = document.getElementById("mobileMenu");
  const menuIcon = document.getElementById("menuIcon");

  if (!menuBtn || !mobileMenu || !menuIcon) {
    return;
  }

  menuBtn.addEventListener("click", () => {
    mobileMenu.classList.toggle("hidden");

    if (mobileMenu.classList.contains("hidden")) {
      menuIcon.classList.remove("fa-xmark");
      menuIcon.classList.add("fa-bars");
    } else {
      menuIcon.classList.remove("fa-bars");
      menuIcon.classList.add("fa-xmark");
    }
  });
}

// ============================
// ACTIVE LINK
// ============================

function setupActiveLinks() {
  const links = document.querySelectorAll(".nav-link");

  const currentPath = window.location.pathname;

  links.forEach((link) => {
    const linkPath = new URL(link.getAttribute("href"), window.location.href)
      .pathname;

    if (linkPath === currentPath) {
      link.classList.add("text-[#4d6fff]", "border-b-2", "border-[#4d6fff]");
    }
  });
}
