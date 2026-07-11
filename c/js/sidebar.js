function createSidebar(activeIndex = 0) {

  // CSS Inject
  if (!document.getElementById("sidebar-style")) {
    const style = document.createElement("style");
    style.id = "sidebar-style";
    style.innerHTML = `
      .custom-scrollbar::-webkit-scrollbar{
        width:3px;
      }

      .custom-scrollbar::-webkit-scrollbar-track{
        background:#020617;
      }

      .custom-scrollbar::-webkit-scrollbar-thumb{
        background:#264dff;
        border-radius:10px;
      }

      .custom-scrollbar{
        scrollbar-width:thin;
        scrollbar-color:#264dff #020617;
      }
    `;
    document.head.appendChild(style);
  }

  const menus = [
    { name: "C HOME", link: "home.html" },
    { name: "C Intro", link: "intro.html" },
    { name: "C Get Started", link: "getstarted.html" },

    {
      name: "C Syntax" ,
      submenu: [
        // { name: "Syntax", link: "Syntax.html" },
        { name: "Statements", link: "statements.html" },
        { name: "Syntax Code Challenge", link: "syntax-challenge.html" }
      ]
    },

    { name: "C Output", link: "output.html" },
    { name: "C Comments", link: "comments.html" },
    { name: "C Variables", link: "variables.html" },
    { name: "C User Input", link: "user-input.html" },
    { name: "C Data Types", link: "data-types.html" },
    { name: "C Operators", link: "operators.html" }
  ];

  let menuHTML = "";

  menus.forEach((menu, index) => {

// syntax
    if (menu.submenu) {

      menuHTML += `
        <div>

          <button
            class="submenu-btn  w-full flex justify-between items-center py-[18px] px-[35px]
            text-white text-[18px] font-bold rounded-[18px] hover:bg-[#3d5dff] transition"
          >
            <span>${menu.name}</span>
            <i class="text-xl text-[#9c9a9a] fa-solid fa-caret-right"></i>
          </button>

          <div class="submenu hidden ml-8 mt-2 flex flex-col gap-2">
      `;
// syntax drop down
      menu.submenu.forEach(item => {
        menuHTML += `
          <a
            href="${item.link}"
            class="block px-4 py-2 rounded-lg font-bold text-gray-300 hover:bg-[#3158ff] hover:text-white transition"
          >
             ${item.name}
          </a>
        `;
      });

      menuHTML += `
          </div>

        </div>
      `;

    } else {
   // sidebar link a tag sab
      menuHTML += `
        <a
          href="${menu.link}"
          class="block py-[18px] px-[35px] text-white text-[18px] font-bold rounded-[18px]
          ${index === activeIndex ? "bg-[#3d5dff]" : "hover:bg-[#3d5dff]"}
          transition duration-300"
        >
          ${menu.name}
        </a>
      `;

    }

  });
// sidebar 
  return `
    <aside class="hidden md:block sticky top-[110px] left-0 w-[300px] h-[calc(100vh-110px)] overflow-y-auto custom-scrollbar bg-[#020b24] p-5 z-[50]" >
     <h5 class="text-[#7f8db4] tracking-[5px] ml-10 text-[20px] font-bold mb-5 uppercase"> C Tutorial </h5>
      <nav class="flex flex-col gap-3 "> ${menuHTML} </nav>
       </aside>
  `;
}

// Dropdown Function
document.addEventListener("click", function (e) {

  const btn = e.target.closest(".submenu-btn");
  if (!btn) return;

  const submenu = btn.nextElementSibling;
  const icon = btn.querySelector("i");

  submenu.classList.toggle("hidden");

  icon.classList.toggle("fa-caret-right");
  icon.classList.toggle("fa-caret-down");

  // Background active
  if (submenu.classList.contains("hidden")) {
    btn.style.backgroundColor = "";
  } else {
    btn.style.backgroundColor = "#3d5dff";
  }

});