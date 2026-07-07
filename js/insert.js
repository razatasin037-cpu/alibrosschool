// ============================
// NAVBAR
// ============================

fetch("/navbar.html")
  .then((res) => res.text())
  .then((data) => {
    document.getElementById("navbar").innerHTML = data;

    setupMobileMenu();
    setupActiveLinks();
    initLanguage();
  })
  .catch((err) => console.error(err));

// ============================
// FOOTER
// ============================

fetch("/footer.html")
  .then((res) => res.text())
  .then((data) => {
    document.getElementById("footer").innerHTML = data;
  });

// ============================
// MOBILE MENU
// ============================

function setupMobileMenu() {
  const menuBtn = document.getElementById("menuBtn");
  const mobileMenu = document.getElementById("mobileMenu");
  const menuIcon = document.getElementById("menuIcon");

  if (!menuBtn) return;

  menuBtn.addEventListener("click", () => {
    mobileMenu.classList.toggle("hidden");

    if (mobileMenu.classList.contains("hidden")) {
      menuIcon.classList.replace("fa-xmark", "fa-bars");
    } else {
      menuIcon.classList.replace("fa-bars", "fa-xmark");
    }
  });
}

// ============================
// ACTIVE LINK
// ============================

function setupActiveLinks() {
  const links = document.querySelectorAll(".nav-link");

  const current = window.location.pathname;

  links.forEach((link) => {
    if (link.getAttribute("href") === current) {
      link.classList.add(
        "text-[#4d6fff]",
        "border-b-2",
        "border-[#4d6fff]"
      );
    }
  });
}
