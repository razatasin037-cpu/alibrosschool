// ============================
// NAVBAR
// ============================

const jsPath = document.currentScript.src;
const projectPath = new URL("../", jsPath);

fetch(new URL("navbar.html", projectPath))
  .then((res) => {
    if (!res.ok) {
      throw new Error("Navbar load nahi hua");
    }
    return res.text();
  })
  .then((data) => {
    const navbar = document.getElementById("navbar");

    if (navbar) {
      navbar.innerHTML = data;

      // Navbar load hone ke BAAD
      setupMobileMenu();
      setupActiveLinks();
    }
  })
  .catch((err) => {
    console.error("Navbar Error:", err);
  });

// ============================
// FOOTER
// ============================

fetch(new URL("footer.html", projectPath))
  .then((res) => {
    if (!res.ok) {
      throw new Error("Footer load nahi hua");
    }
    return res.text();
  })
  .then((data) => {
    const footer = document.getElementById("footer");

    if (footer) {
      footer.innerHTML = data;
    }
  })
  .catch((err) => {
    console.error("Footer Error:", err);
  });

// ============================
// ACTIVE NAV LINK
// ============================
// ============================
// ACTIVE NAV LINK
// ============================
function setupActiveLinks() {
  const currentPath = window.location.pathname;

  document.querySelectorAll("#navbar .nav-link").forEach((link) => {
    const href = link.getAttribute("href");

    if (!href || href === "#") return;

    const linkPath = new URL(href, window.location.origin).pathname;

    if (currentPath === linkPath || currentPath.endsWith(linkPath)) {
      link.classList.add("active");
    } else {
      link.classList.remove("active");
    }
  });
}

// ============================
// MOBILE MENU
// ============================

function setupMobileMenu() {
  const menuBtn = document.getElementById("menuBtn");
  const mobileMenu = document.getElementById("mobileMenu");

  if (!menuBtn || !mobileMenu) {
    return;
  }

  menuBtn.addEventListener("click", () => {
    mobileMenu.classList.toggle("hidden");
  });
}
fetch(new URL("navbar.html", projectPath))
  .then((res) => res.text())
  .then((data) => {
    document.getElementById("navbar").innerHTML = data;

    // IMPORTANT
    setupMobileMenu();
    setupActiveLinks();
  });
