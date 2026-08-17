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
    { name: "C++ HOME", link: "c++tutoreal.html" },
    { name: "C++ Intro", link: "intro.html" },
    { name: "C++ Get Started", link: "start.html" },

    {
      name: "C++ Syntax",
      link: "syntax.html",
      submenu: [
        { name: "Syntax", link: "syntax.html" },
        { name: "Statements", link: "statements.html" },
        { name: "Syntax Code Challenge", link: "syntaxCodeChallenge.html" },
      ],
    },

    {
      name: "C++ Output",
      link: "printText.html",
      submenu: [
        { name: "Print Text", link: "printText.html" },
        { name: "New Lines", link: "newLines.html" },
      ],
    },

    { name: "C++ Comments", link: "comments.html" },

    {
      name: "C++ Variables",
      link: "createvariables.html",
      submenu: [
        { name: "Create Variables", link: "createvariables.html" },
        { name: "Format Specifiers", link: "formatspecifiers.html" },
        { name: "Format Specifiers", link: "formatspecifiers.html" },
      ],
    },

    { name: "C++ User Input", link: "user-input.html" },
    { name: "C++ Data Types", link: "data-types.html" },
    { name: "C++ Operators", link: "operators.html" },
  ];
  const currentPage = window.location.pathname.toLowerCase();

  let menuHTML = "";

  menus.forEach((menu, index) => {
    // submenu k liye
    if (menu.submenu) {
      const isOpen =
        currentPage.includes(menu.link.toLowerCase()) ||
        menu.submenu.some((item) =>
          currentPage.includes(item.link.toLowerCase()),
        );

      menuHTML += `
    <div>

    <div
  class="group flex items-center rounded-[18px] overflow-hidden
  ${
    isOpen
      ? "bg-white/10" //c syntax
      : "hover:bg-[#3d5dff]"
  } transition duration-200">

  <a
    href="${menu.link}"
    class="flex-1 py-[18px] px-[35px] text-white text-[18px] font-bold">

    ${menu.name}

  </a>

  <button
    class="submenu-btn px-5 py-[18px] text-white">

    <i class="fa-solid ${isOpen ? "fa-caret-down" : "fa-caret-right"}"></i>

  </button>

</div>


      <div class="submenu ${
        isOpen ? "" : "hidden"
      } ml-8 mt-2 flex flex-col gap-2">
  `;
      // submenu k liye
      menu.submenu.forEach((item) => {
        const active = currentPage.includes(item.link.toLowerCase());

        menuHTML += `
      <a
        href="${item.link}"
        class="block px-4 py-2 rounded-lg font-bold transition
        ${
          active
            ? "bg-[#3d5dff] text-white"
            : "text-gray-300 hover:bg-[#3158ff] hover:text-white"
        }">

        ${item.name}

      </a>
    `;
      });

      menuHTML += `
      </div>

    </div>
  `;
    }
    // sab k liye
    else {
      menuHTML += `
      <a
        href="${menu.link}"
        class="block py-[18px] px-[35px]
        text-white text-[18px] font-bold rounded-[18px]
        ${index === activeIndex ? "bg-[#3d5dff]" : "hover:bg-[#3d5dff]"}
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
      C++ Tutorial
    </h5>

    <nav class="flex flex-col gap-3">

      ${menuHTML}

    </nav>

  </aside>
  `;
}

// Dropdown
document.addEventListener("click", function (e) {
  const btn = e.target.closest(".submenu-btn");

  if (!btn) return;

  const submenu = btn.parentElement.nextElementSibling;

  const icon = btn.querySelector("i");

  submenu.classList.toggle("hidden");

  icon.classList.toggle("fa-caret-right");
  icon.classList.toggle("fa-caret-down");
});
