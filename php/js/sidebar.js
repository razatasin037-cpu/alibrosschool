function createSidebar(activeIndex = 0) {
  if (!document.getElementById("sidebar-style")) {
    const style = document.createElement("style");
    style.id = "sidebar-style";
    style.innerHTML = `
      .custom-scrollbar::-webkit-scrollbar { 
        width: 3px; 
      }
      .custom-scrollbar::-webkit-scrollbar-track { 
        background: #020617; 
      }
      .custom-scrollbar::-webkit-scrollbar-thumb { 
        background: #264dff; 
        border-radius: 10px; 
      }
      .custom-scrollbar { 
        scrollbar-width: thin; 
        scrollbar-color: #264dff #020617; 
      }

      /* Smooth Submenu Dropdown Animation */
      .submenu-wrapper {
        transition: max-height 0.4s ease-in-out, opacity 0.35s ease-in-out, margin 0.3s ease;
        max-height: 0;
        opacity: 0;
        overflow: hidden;
      }
      .submenu-wrapper.open {
        max-height: 500px;
        opacity: 1;
      }
    `;
    document.head.appendChild(style);
  }

  const menus = [
    { name: "PHP HOME", link: "/php/home/home.html" },
    { name: "PHP Intro", link: "/php/intro/intro.html" },
    { name: "PHP Install", link: "/php/install/install.html" },
    { name: "PHP Syntax", link: "/php/syntax/syntax.html" },
    
    { name: "PHP Comments",
      link: "/php/comments/comments.html",
      submenu: [
        { name: "PHP Comments", link: "/php/comments/Comments.html" },
        { name: "PHP Multiline", link: "/php/comments/Multiline.html" }
      ]
    },

    {
      name: "PHP Variables",
      link: "/php/variables/Variables.html",
      submenu: [
        { name: "Variables", link: "/php/variables/Variables.html" },
        { name: "Variables Scope", link: "/php/variables/VariablesScope.html" }
      ]
    },

    { name: "PHP Echo / Print", link: "/php/echoprint/EchoPrint.html" },
    { name: "PHP Data Types", link: "/php/datatypes/Data-Types.html" },

    {
      name: "PHP Strings",
      link: "/php/strings/Strings.html",
      submenu: [
        { name: "PHP Strings", link: "/php/strings/Strings.html" },
        { name: "Strings Functions", link: "/php/strings/StringFunctions.html" },
        { name: "Modify String", link: "/php/strings/ModifyString.html" },
        { name: "Escape Characters", link: "/php/strings/EscapeCharacters.html"}
      ]
    },

    { name: "PHP Numbers ", link: "/php/numbers/Numbers.html" },

    { name: "PHP Casting", link: "/php/casting/Casting.html" },
    { name: "PHP Maths", link: "/php/maths/Maths.html" },
    { name: "PHP Constants", link: "/php/constants/Constants.html" },
    

    { name: "PHP Operators", link: "/php/operators/Operators.html"},
    {
      name: "PHP If...Else",
      link: "/php/if_else/If.html",
      submenu: [
        { name: "If", link: "/php/if_else/If.html" },
        { name: "If...Else", link: "/php/if_else/if...Else.html" },
        { name: "Shorthand If", link: "/php/if_else/ShorthandIf.html" },
        { name: "Nested If", link: "/php/if_else/NestedIf.html" }
      ]
    },
    { name: "PHP Switch", link: "/php/switch/Switch.html"},
    {
      name: "PHP Loop",
      link: "/php/loops/Loops.html",
      submenu: [
        { name: "Loops", link: "/php/loops/Loops.html" },
        { name: "While Loop", link: "/php/loops/WhileLoop.html" },
        { name: "Do-While Loop", link: "/php/loops/DoWhile.html" },
        { name: "For Loop", link: "/php/loops/ForLoop.html" },
        { name: "foreach Loop", link: "/php/loops/ForeachLoop.html" },
       { name: "Break Statement", link: "/php/loops/BreakStatement.html" }

      ]
    },
    { name: "PHP Functions", link: "/php/functions/Functions.html" },
    
    {
      name: "PHP Arrays",
      link: "/php/arrays/Arrays.html",
      submenu: [
        { name: "Arrays", link: "/php/arrays/Arrays.html" },
        { name: "Indexed Arrays", link: "/php/arrays/IndexedArrays.html" },
        { name: "Access Arrays ", link: "/php/arrays/AccessArrays.html" },
        { name: "Update Array Items ", link: "/php/arrays/UpdateArrayItems.html" },
        { name: "Add Array Items ", link: "/php/arrays/AddArrayItems.html" },
        { name: "Remove Array Items ", link: "/php/arrays/RemoveArrayItems.html" },
        {name: "Sorting Arrays", link: "/php/arrays/SortingArrays.html"},
        {name: "Multidimensional Arrays", link: "/php/arrays/Multidimensional.html"},
        {name: "Arrays Functions", link: "/php/arrays/ArrayFunctions.html"}
      ]
    },
    {
      name: "PHP Superglobals",
      link: "/php/superglobals/Superglobals.html",
      submenu: [
        { name: "Superglobals", link: "/php/superglobals/Superglobals.html" },
        { name: "$GLOBALS", link: "/php/superglobals/Globals.html" },
        { name: "$_SERVER", link: "/php/superglobals/Server.html" },
        { name: "$_REQUEST", link: "/php/superglobals/Request.html" },
        { name: "$_POST", link: "/php/superglobals/Post.html" },
        { name: "$_GET", link: "/php/superglobals/Get.html" }
      ]
    },
    
    {
      name: "PHP Forms",
      link: "/php/forms/FormHandling.html",
      submenu: [
        { name: "Form Handling", link: "/php/forms/FormHandling.html" },
        { name: "Form Validation", link: "/php/forms/FormValidation.html" },
        { name: " Forms - Required", link: "/php/forms/FormsRequired.html" },
        { name: "Forms - Validate E-mail and URL", link: "/php/forms/FormsValidateE-mailandURL.html" },
        { name:"Form  Complete", link:"/php/forms/FormComplete.html"}
      ]
    },
    {
      name: " PHP Advanced",
      link: "/php/advanceds/TimeAndDate.html",
      submenu: [
        { name: "PHP Time And Date", link: "/php/advanceds/TimeAndDate.html" },
        { name: "PHP Include", link: "/php/advanceds/Include.html" },
        { name: "PHP File Handling", link: "/php/advanceds/FileHandling.html" },
        { name: "PHP File Open/Read ", link: "/php/advanceds/FileOpenRead.html" },
        { name:"PHP File Upload", link:"/php/advanceds/FileUpload.html"},
        { name: "PHP Cookies", link:"/php/advanceds/Cookies.html"},
        { name: "PHP Sessions", link: "/php/advanceds/Sessions.html"},
        { name: "PHP Filters", link:"/php/advanceds/Filters.html"},
        { name: "PHP JSON", link:"/php/advanceds/JSON.html"},
        { name: "PHP Exceptions", link:"/php/advanceds/Exceptions.html"}
      
      ]
    }
   
  ];

  // Get exact current page file name
  const currentPath = window.location.pathname.toLowerCase();
  const currentFileName = currentPath.split('/').pop();

  let menuHTML = "";

  menus.forEach((menu, index) => {
    if (menu.submenu) {
      // Check if current file matches main link OR any sub-link
      const mainFileName = menu.link.split('/').pop().toLowerCase();
      const isParentActive = mainFileName === currentFileName;
      
      const isSubmenuActive = menu.submenu.some(item => {
        const itemFileName = item.link.split('/').pop().toLowerCase();
        return itemFileName === currentFileName;
      });

      // Active state OR activeIndex logic
      const isOpen = isParentActive || isSubmenuActive || index === activeIndex;

      menuHTML += `
        <div>
          <div class="group flex items-center rounded-[18px] overflow-hidden ${
            isOpen ? "bg-white/10" : "hover:bg-[#3d5dff]"
          } transition duration-200">

            <a href="${menu.link}" class="sidebar-link flex-1 py-[18px] px-[35px] text-white text-[18px] font-bold">
              ${menu.name}
            </a>

            <button type="button" class="submenu-btn px-5 py-[18px] text-white focus:outline-none">
              <i class="fa-solid ${
                isOpen ? "fa-caret-down" : "fa-caret-right"
              }"></i>
            </button>

          </div>

          <div class="submenu-wrapper ${
            isOpen ? "open mt-2" : ""
          } ml-8 flex flex-col gap-2">
      `;

      menu.submenu.forEach(item => {
        const itemFileName = item.link.split('/').pop().toLowerCase();
        const active = currentFileName === itemFileName;

        menuHTML += `
          <a href="${item.link}" class="sidebar-link block px-4 py-2 rounded-lg font-bold transition ${
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
    } else {
      const mainFileName = menu.link.split('/').pop().toLowerCase();
      const isMainActive = mainFileName === currentFileName || index === activeIndex;

      menuHTML += `
        <a href="${menu.link}" class="sidebar-link block py-[18px] px-[35px] text-white text-[18px] font-bold rounded-[18px] ${
          isMainActive ? "bg-[#3d5dff]" : "hover:bg-[#3d5dff]"
        } transition">
          ${menu.name}
        </a>
      `;
    }
  });

  return `
    <aside id="sidebar-container" class="hidden md:block sticky top-[110px] w-[300px] h-[calc(100vh-110px)] overflow-y-auto custom-scrollbar bg-[#020b24] p-5">
      <h5 class="text-[#7f8db4] tracking-[5px] text-[20px] font-bold mb-5 ml-10 uppercase">
          PHP TUTORIAL
      </h5>
      <nav class="flex flex-col gap-3">
        ${menuHTML}
      </nav>
    </aside>
  `;
}

// Submenu Dropdown Toggle (Caret Icon Par Click Hone Par Open/Close Ho)
document.addEventListener("click", function (e) {
  const btn = e.target.closest(".submenu-btn");
  if (!btn) return;

  e.preventDefault();
  e.stopPropagation();

  const submenu = btn.parentElement.nextElementSibling;
  const icon = btn.querySelector("i");

  if (submenu) {
    submenu.classList.toggle("open");
    submenu.classList.toggle("mt-2");
  }

  if (icon) {
    icon.classList.toggle("fa-caret-right");
    icon.classList.toggle("fa-caret-down");
  }
});

// Scroll Position Save Handler
document.addEventListener("click", function (e) {
  const link = e.target.closest(".sidebar-link");
  if (link) {
    const sidebar = document.getElementById("sidebar-container");
    if (sidebar) {
      sessionStorage.setItem("sidebarScrollTop", sidebar.scrollTop);
    }
  }
});

// Scroll Position Restore Handler
window.addEventListener("DOMContentLoaded", function () {
  const savedScroll = sessionStorage.getItem("sidebarScrollTop");
  const sidebar = document.getElementById("sidebar-container");

  if (sidebar && savedScroll !== null) {
    sidebar.scrollTop = parseInt(savedScroll, 10);
  }
});