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
    { name: "HTML HOME", link: "/html/home/home.html" },
    { name: "HTML Intro", link: "/html/intro/Intro.html" },
    { name: "HTML Editors", link: "/html/editors/Editors.html" },
    { name: "HTML Elements", link: "/html/elements/Elements.html" },
    { name: "HTML Attributes", link: "/html/attributes/Attributes.html" },
    { name: "HTML Headings", link: "/html/headings/Headings.html" },
    { name: "HTML Paragraphs", link: "/html/paragraphs/Paragraphs.html" },
    { name: "HTML Styles", link: "/html/styles/Styles.html" },
    { name: "HTML Formatting", link: "/html/formatting/Formatting.html" },
    { name: "HTML Quotation", link: "/html/quotation/Quotation.html" },
    { name: "HTML Comments", link: "/html/comments/Comments.html"},
    { name: "HTML Colors", link: "/html/colors/Colors.html" },
    { name: "HTML CSS", link: "/html/css/Css.html" },
    { name: "HTML Links", link: "/html/links/Links.html" },
// images
    { name: "HTML Images",
     link: "/html/images/Images.html",
        submenu: [
        {  name: "Images", link: "/html/images/Images.html", },
        { name: "Image Map", link: "/html/images/ImageMap.html" },
        { name: "Background Images", link: "/html/images/BackgroundImages.html" },
        { name: "The Picture Element", link:"/html/images/PictureElement.html"}
      ]
    },
    //favicon
    { name: "HTML Favicon", link: "/html/favicon/Favicon.html" },
    // page title
    { name: "HTML Page Title", link: "/html/pageTitle/PageTitle.html" },
// tables
    { name: "HTML Tables",
     link:"/html/tables/Tables.html",
     submenu: [
        { name: "HTML Tables", link:"/html/tables/Tables.html" },
        { name: "Table Borders", link: "/html/tables/TableBorders.html" },
        { name: "Table Sizes", link: "/html/tables/TableSizes.html"},
        { name: "Table Headers", link: "/html/tables/TableHeaders.html"},
        { name: "Table Styling", link: "/html/tables/TableStyling.html"},
        { name: "Table Colgroup", link: "/html/tables/TableColgroup.html"},
        { name: "Table Colspan & Rowspan", link: "/html/tables/TableColspan.html"},
        
      ]
    },
    // list
    {
      name: "HTML Lists",
      link: "/html/lists/Lists.html",
      submenu: [
        { name: "Lists", link: "/html/lists/Lists.html" },
        { name: "Unordered Lists", link: "/html/lists/UnorderedLists.html" },
        { name: "Ordered Lists", link: "/html/lists/OrderedLists.html" },
        { name: "Other Lists", link: "/html/lists/OtherLists.html" }
      ]
    },
// blobk & inline
    { name: "HTML Block & Inline", link: "/html/blockInline/BlockInline.html" },
// div
    { name: "HTML Div", link: "/html/div/Div.html" },
    // Classes
    { name: "HTML Classes", link: "/html/classes/Classes.html" },
    //id
    { name: "HTML Id", link: "/html/id/Id.html" },
    // Buttons
    { name: "HTML Buttons", link: "/html/buttons/Buttons.html"},
    //Iframes
    { name: "HTML Iframes", link: "/html/iframes/Iframes.html"},
    // JavaScrpit
     { name: "HTML JavaScript", link: "/html/javaScript/JavaScript.html"},
     //File Paths
     { name: "HTML File Paths", link: "/html/filePaths/FilePaths.html"},
     // Head
    { name: "HTML Head ", link: "/html/head/Head.html"},
    // Layout 
    { name: "HTML Layout", link: "/html/layout/Layout.html"},
    // Responsive 
    { name: "HTML Responsive", link: "/html/responsive/Responsive.html"},

    {
      name: "HTML Forms",
      link: "/html/forms/Forms.html",
      submenu: [
        { name: "HTML Forms", link: "/html/forms/Forms.html" },
        { name: "HTML Form Attributes ", link: "/html/forms/Attributes.html" },
        { name: "HTML Form Elements", link: "/html/forms/Elements.html" },
        { name: "HTML Input Types", link: "/html/forms/InputTypes.html" },
        { name:"HTML Input Attributes", link:"/html/forms/Input-Attributes.html"}
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
      <h5 class="text-[#7f8db4] tracking-[5px] text-[20px] font-bold mb-5 ml-6 uppercase">
          HTML TUTORIAL
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