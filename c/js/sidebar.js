function createSidebar(activeIndex = 0) {

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
      name: "C Syntax",
      submenu: [
        { name: "Syntax", link: "syntax.html" },
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

  const currentPage = window.location.pathname.toLowerCase();

  let menuHTML = "";

  menus.forEach((menu, index) => {

    if (menu.submenu) {

      const isOpen = menu.submenu.some(item =>
        currentPage.includes(item.link.toLowerCase())
      );

      menuHTML += `
      <div>

        <button
          class="submenu-btn w-full flex justify-between items-center
          py-[18px] px-[35px]
          text-white text-[18px] font-bold rounded-[18px]
          ${!isOpen ? "hover:bg-[#3d5dff]" : ""}
          transition"
        >

          <span>${menu.name}</span>

          <i class="fa-solid ${
            isOpen ? "fa-caret-down" : "fa-caret-right"
          } text-xl text-gray-400"></i>

        </button>

        <div class="submenu ${isOpen ? "" : "hidden"} ml-8 mt-2 flex flex-col gap-2">
      `;

      menu.submenu.forEach(item => {

        const active = currentPage.includes(item.link.toLowerCase());

        menuHTML += `
        <a
          href="${item.link}"
          class="block px-4 py-2 rounded-lg font-bold transition
          ${
            active
            ? "bg-[#3d5dff] text-white"
            : "text-gray-300 hover:bg-[#3158ff] hover:text-white"
          }"
        >
          ${item.name}
        </a>
        `;
      });

      menuHTML += `
        </div>

      </div>
      `;

    }

    else {

      menuHTML += `
      <a
        href="${menu.link}"
        class="block py-[18px] px-[35px]
        text-white text-[18px] font-bold rounded-[18px]
        ${index===activeIndex
          ? "bg-[#3d5dff]"
          : "hover:bg-[#3d5dff]"
        }
        transition"
      >
        ${menu.name}
      </a>
      `;
    }

  });

  return `
  <aside
  class="hidden md:block sticky top-[110px]
  w-[300px] h-[calc(100vh-110px)]
  overflow-y-auto custom-scrollbar
  bg-[#020b24] p-5">

    <h5 class="text-[#7f8db4] tracking-[5px]
    text-[20px] font-bold mb-5 ml-10 uppercase">
      C Tutorial
    </h5>

    <nav class="flex flex-col gap-3">

      ${menuHTML}

    </nav>

  </aside>
  `;
}


// Dropdown
document.addEventListener("click", function(e){

    const btn=e.target.closest(".submenu-btn");

    if(!btn) return;

    const submenu=btn.nextElementSibling;

    const icon=btn.querySelector("i");

    submenu.classList.toggle("hidden");

    icon.classList.toggle("fa-caret-right");

    icon.classList.toggle("fa-caret-down");

});