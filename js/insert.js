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
  const langBtn = document.getElementById("langBtn");
  const langMenu = document.getElementById("langMenu");
  const currentLang = document.getElementById("currentLang");

  const englishBtn = document.getElementById("englishBtn");
  const hinglishBtn = document.getElementById("hinglishBtn");

  if (!langBtn || !langMenu) return;

  // Dropdown Open/Close
  langBtn.addEventListener("click", (e) => {
    e.stopPropagation();
    langMenu.classList.toggle("hidden");
  });

  // English Select
  englishBtn.addEventListener("click", () => {
    currentLang.textContent = "English";
    langMenu.classList.add("hidden");
  });

  // Hinglish Select
  hinglishBtn.addEventListener("click", () => {
    currentLang.textContent = "Hinglish";
    langMenu.classList.add("hidden");
  });

  // Outside Click
  document.addEventListener("click", () => {
    langMenu.classList.add("hidden");
  });
}