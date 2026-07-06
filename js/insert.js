// 1. NAVBAR FETCH & LOGIC
fetch("/navbar.html")
  .then(res => res.text())
  .then(data => {
    // Pehle HTML ko page par insert karo
    document.getElementById("navbar").innerHTML = data;

    // JAISE HI NAVBAR INJECT HO JAYE, USKE TURANT BAAD LOGIC INITIALIZE KARO
    setupMobileMenu();
    setupActiveLinks();
    initLanguage(); 
  })
  .catch(err => console.error("Navbar load karne me dikkat aayi:", err));

// 2. FOOTER FETCH
fetch("/footer.html")
  .then(res => res.text())
  .then(data => {
    document.getElementById("footer").innerHTML = data;
  })
  .catch(err => console.error("Footer load karne me dikkat aayi:", err));


// --- INJECTION KE BAAD CHALNE WALE FUNCTIONS ---

// Mobile Hamburger Menu Setup
function setupMobileMenu() {
  const menuBtn = document.getElementById('menuBtn');
  const mobileMenu = document.getElementById('mobileMenu');
  const menuIcon = document.getElementById('menuIcon');

  // Pehle check karenge ki element sach me HTML me aa chuke hain ya nahi
  if (menuBtn && mobileMenu && menuIcon) {
    menuBtn.addEventListener('click', () => {
      // Open/Close toggle
      mobileMenu.classList.toggle('hidden');
      
      // Icon transformation logic
      if (mobileMenu.classList.contains('hidden')) {
        menuIcon.classList.remove('fa-xmark');
        menuIcon.classList.add('fa-bars');
      } else {
        menuIcon.classList.remove('fa-bars');
        menuIcon.classList.add('fa-xmark');
      }
    });
  }
}
function setupActiveLinks() {
  const navLinks = document.querySelectorAll(".nav-link");

  const activeClasses = ["border-b-2", "border-[#4d6fff]", "text-[#4d6fff]"];

  // 👉 Step 1: Page load par active set karo
  const currentPath = window.location.pathname;

  navLinks.forEach(link => {
    // clean old active classes
    link.classList.remove(...activeClasses);

    // agar href current page se match kare
    if (link.getAttribute("href") === currentPath) {
      link.classList.add(...activeClasses);
    }

    // 👉 Step 2: click handler
    link.addEventListener("click", function () {
      navLinks.forEach(item => item.classList.remove(...activeClasses));
      this.classList.add(...activeClasses);
    });
  });
}



//  LANGUAGE TOGGLE LOGIC 

// ================= LANGUAGE DROPDOWN =================
function initLanguage() {

  function setupDropdown(btnId, menuId, currentId, englishId, hinglishId) {

    const btn = document.getElementById(btnId);
    const menu = document.getElementById(menuId);
    const current = document.getElementById(currentId);
    const english = document.getElementById(englishId);
    const hinglish = document.getElementById(hinglishId);

    if (!btn || !menu) return;

    btn.addEventListener("click", (e) => {
      e.stopPropagation();
      menu.classList.toggle("hidden");
    });

    english.addEventListener("click", () => {
      current.textContent = "English";
      menu.classList.add("hidden");
    });

    hinglish.addEventListener("click", () => {
      current.textContent = "Hinglish";
      menu.classList.add("hidden");
    });

    document.addEventListener("click", () => {
      menu.classList.add("hidden");
    });

    menu.addEventListener("click", (e) => {
      e.stopPropagation();
    });

  }

  // Desktop
  setupDropdown(
    "langBtn",
    "langMenu",
    "currentLang",
    "englishBtn",
    "hinglishBtn"
  );

  // Mobile
  setupDropdown(
    "mobileLangBtn",
    "mobileLangMenu",
    "mobileCurrentLang",
    "mobileEnglishBtn",
    "mobileHinglishBtn"
  );
}