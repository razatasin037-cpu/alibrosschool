function createSidebar(activeIndex = 0) {
  const menus = [
    { name: "C HOME", link: "home.html" },
    { name: "C Intro", link: "intro.html" },
    { name: "C Get Started", link: "get-started.html" },
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
      bg-[#020b24] p-5 border-r border-[#264dff] z-[50]"
    >
      <h5
        class="text-[#7f8db4] tracking-[5px] text-[20px] font-bold mb-5 uppercase"
      >
        C Tutorial
      </h5>

      <nav class="flex flex-col gap-3">
        ${menuHTML}
      </nav>
    </aside>
  `;
}