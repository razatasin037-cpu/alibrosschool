// 1. NAVBAR FETCH & LOGIC
fetch("/navbar.html")
  .then(res => res.text())
  .then(data => {
    // Pehle HTML ko page par insert karo
    document.getElementById("navbar").innerHTML = data;

    // JAISE HI NAVBAR INJECT HO JAYE, USKE TURANT BAAD LOGIC INITIALIZE KARO
    setupMobileMenu();
    setupActiveLinks();
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

// Active Links Setup
function setupActiveLinks() {
  const navLinks = document.querySelectorAll(".nav-link");
  if (navLinks.length > 0) {
    navLinks.forEach(link => {
      link.addEventListener("click", function() {
        navLinks.forEach(item => item.classList.remove("active"));
        this.classList.add("active");
      });
    });
  }
}