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

// ============================
// LANGUAGE
// ============================

function initLanguage() {

  function setupDropdown(
    btnId,
    menuId,
    currentId,
    englishId,
    hinglishId
  ) {

    const btn = document.getElementById(btnId);
    const menu = document.getElementById(menuId);
    const current = document.getElementById(currentId);

    const english = document.getElementById(englishId);
    const hinglish = document.getElementById(hinglishId);

    if (!btn) return;

    btn.addEventListener("click", (e) => {
      e.stopPropagation();
      menu.classList.toggle("hidden");
    });

    english.addEventListener("click", () => {
      current.textContent = "English";

      menu.classList.add("hidden");

      setLanguage("en");
    });

    hinglish.addEventListener("click", () => {
      current.textContent = "Hinglish";

      menu.classList.add("hidden");

      setLanguage("hi");
    });

    menu.addEventListener("click", (e) => {
      e.stopPropagation();
    });

    document.addEventListener("click", () => {
      menu.classList.add("hidden");
    });
  }

  setupDropdown(
    "langBtn",
    "langMenu",
    "currentLang",
    "englishBtn",
    "hinglishBtn"
  );

  setupDropdown(
    "mobileLangBtn",
    "mobileLangMenu",
    "mobileCurrentLang",
    "mobileEnglishBtn",
    "mobileHinglishBtn"
  );

  const saved = localStorage.getItem("language") || "en";

  setLanguage(saved);
}

// ============================
// CHANGE LANGUAGE
// ============================

function setLanguage(lang) {

  const english = document.querySelectorAll(".lang-en");
  const hinglish = document.querySelectorAll(".lang-hi");

  if (lang === "en") {

    english.forEach((item) => item.classList.remove("hidden"));

    hinglish.forEach((item) => item.classList.add("hidden"));

  } else {

    english.forEach((item) => item.classList.add("hidden"));

    hinglish.forEach((item) => item.classList.remove("hidden"));
  }

  const desktop = document.getElementById("currentLang");
  const mobile = document.getElementById("mobileCurrentLang");

  if (desktop)
    desktop.textContent = lang === "en" ? "English" : "Hinglish";

  if (mobile)
    mobile.textContent = lang === "en" ? "English" : "Hinglish";

  localStorage.setItem("language", lang);
}