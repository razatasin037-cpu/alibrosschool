function createSidebar(activeIndex = 0) {

  // CSS Inject
  if (!document.getElementById("sidebar-style")) {
    const style = document.createElement("style");
    style.id = "sidebar-style";
    style.innerHTML = `
      .nav-link-active::after{
        content:"";
        position:absolute;
        left:0;
        bottom:-8px;
        width:100%;
        height:2px;
        background:#4d6fff;
        border-radius:10px;
      }

      .custom-scrollbar::-webkit-scrollbar{
         width:3px;     
         height:3px; 
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
    { name: "C Syntax", link: "syntax.html" },
    { name: "C Output", link: "output.html" },
    { name: "C Comments", link: "comments.html" },
    { name: "C Variables", link: "variables.html" },
    { name: "C User Input", link: "user-input.html" },
    { name: "C Data Types", link: "data-types.html" },
    { name: "C Operators", link: "operators.html" },
  ];

  let menuHTML = "";

  menus.forEach((menu, index) => {
    menuHTML += `
      <a
        href="${menu.link}"
        class="block py-[18px] px-[35px] text-white text-[18px] font-medium rounded-[18px]
        ${index === activeIndex ? "bg-[#3d5dff]" : "hover:bg-[#3d5dff]"}
        transition duration-300"
      >
        ${menu.name}
      </a>
    `;
  });

  return `
    <aside
      class="hidden md:block sticky top-[110px] left-0 w-[340px]
      h-[calc(100vh-110px)] overflow-y-auto custom-scrollbar
      bg-[#020b24] p-5  z-[50]"
    >
      <h5 class="text-[#7f8db4] tracking-[5px] text-[20px] font-bold mb-5 uppercase">
        C Tutorial
      </h5>

      <nav class="flex flex-col gap-3">
        ${menuHTML}
      </nav>
    </aside>
  `;
}