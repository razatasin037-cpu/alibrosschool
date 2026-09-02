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

      /* Slow & Smooth Submenu Dropdown Animation */
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
    { name: "C HOME", link: "/c/home/home.html" },
    { name: "C Intro", link: "/c/intro/intro.html" },
    { name: "C Get Started", link: "/c/getStarted/getstarted.html" },
    {
      name: "C Syntax",
      link: "/c/syntax/syntax.html",
      submenu: [
        { name: "Syntax", link: "syntax.html" },
        { name: "Statements", link: "statements.html" },
        { name: "Syntax Code Challenge", link: "syntaxCodeChallenge.html" }
      ]
    },
    {
      name: "C Output",
      link: "/c/output/printText.html",
      submenu: [
        { name: "Print Text", link: "printText.html" },
        { name: "New Lines", link: "newLines.html" }
      ]
    },
    { name: "C Comments", link: "/c/comments/comments.html" },
    {
      name: "C Variables",
      link: "/c/Variables/createVariables.html",
      submenu: [
        { name: "Create Variables", link: "createVariables.html" },
        { name: "Format Specifiers", link: "formatSpecifiers.html" },
        { name: "Change Variable", link: "ChangeVariable.html" },
        { name: "Multiple Variable", link: "MultipleVariable.html" },
        { name: "Variable Names", link: "variableNames.html" },
        { name: "Real-Life Examples", link: "Real-Life-Examples.html" }
      ]
    },
    { name: "C User Input", link: "/c/input/UserInput.html" },
    {
      name: "C Data Types",
      link: "/c/datatypes/data-types.html",
      submenu: [
        { name: "Data Types", link: "data-types.html" },
        { name: "Characters", link: "Characterdatatype.html" },
        { name: "Numbers ", link: "NumericDataType.html" },
        { name: "Decimal Precision", link: "DecimalDataType.html" },
        { name: "Memory Size", link: "MemoryOfSize.html" },
        { name: "Real-Life Examples", link: "Real-LifeDataTypesExamples.html" },
        { name: "Extended Types", link: "ExtendedTypes.html" }
      ]
    },
    { name: "C Type Conversion", link: "/c/typeConversion/TypeConversion.html" },
    { name: "C Constants", link: "/c/Constants/Constants.html" },
    { name: "C Booleans", link: "/c/booleans/Booleans.html" },
    {
      name: "C Operators",
      link: "/c/operators/operators.html",
      submenu: [
        { name: "Operators", link: "operators.html" },
        { name: "Arithmetic ", link: "Arithmetic.html" },
        { name: "Assignment ", link: "Assignment.html" },
        { name: "Comparison  ", link: "Comparison.html" },
        { name: "Logical ", link: "Logical.html" },
        { name: "Precedence", link: "Precedence.html" }
      ]
    },
    {
      name: "C If...Else",
      link: "/c/ifElse/If.html",
      submenu: [
        { name: "If", link: "If.html" },
        { name: "Else ", link: "Else.html" },
        { name: "Else If ", link: "ElseIf.html" },
        { name: "Short Hand If  ", link: "ShortHandIf.html" },
        { name: "Nested If ", link: "NestedIf.html" },
        { name: "Real-Life Examples", link: "Real-Life-Examples.html" }
      ]
    },
    {
      name: "C Switch",
      link: "/c/switch/Switch.html",
      submenu: [
        { name: "Switch", link: "Switch.html" }
      ]
    },
    {
      name: "C Loop",
      link: "/c/loop/WhileLoop.html",
      submenu: [
        { name: "While Loop", link: "WhileLoop.html" },
        { name: "Do-While Loop", link: "DoWhileLoop.html" },
        { name: "For Loop", link: "ForLoop.html" }
      ]
    },
     {
      name: "C Break/Continue",link: "/c/BreakandContinue/BreakandContinue.html",
    },
     {
      name: "C Arrays",
      link: "/c/arrays/Arrays.html",
      submenu: [
        { name: "Arrays ", link: "Arrays.html" },
        { name: "Arrays Size", link: "ArraysSizes.html" },
        { name: "Arrays Loops", link: "ArraysLoops.html" }
      ]
    },
    {
      name: "C Strings",
      link: "/c/strings/Strings.html",
      submenu: [
        { name: "Strings ", link: "Strings.html" },
        { name: "Special Characters", link: "SpecialCharacters.html" },
        { name: "String Functions ", link: "StringFunctions.html" }
      ]
    },

     {
      name: "C Pointers",
      link: "/c/pointers/Pointers.html",
      submenu: [
        { name: "Pointers ", link: "Pointers.html" },
        { name: "Pointers & Arrays", link: "PointersandArrays.html" },
        { name: "Pointer Arithmetic ", link: "PointerArithmetic.html" },
        {name: "Pointer to Pointer", link: "PointertoPointer.html"}
      ]
    },
    {
      name: "C Functions",
      link: "/c/functions/Functions.html",
      submenu: [
        { name: "Functions", link: "Functions.html" },
        { name: "Function Parameters", link: "FunctionParameters.html" },
        { name: "Function Declaration", link: "FunctionDeclaration.html" },
        { name: "Recursion", link: "Recursion.html" }
      ]
    },
    {
      name: "C Structures",
      link: "/c/structures/Structures.html",
      submenu: [
        { name: "Structures", link: "Structures.html" },
        { name: "Nested Structures", link: "NestedStructures.html" },
        { name: "Structures & Strings", link: "StructuresStrings.html" },
        { name: "Real-Life Examples", link: "RealLifeExamples.html" }
      ]
    }
    
    
    
  ];

  // Current File Name Extract Karte Hain (e.g., "dowhileloop.html")
  const pathParts = window.location.pathname.split('/');
  const currentFileName = pathParts[pathParts.length - 1].toLowerCase();

  let menuHTML = "";

  menus.forEach((menu, index) => {
    if (menu.submenu) {
      // Check Karte Hain Ki Submenu Ka Koi Item Exact Match Kar Raha Hai Ya Nahi
      const isSubmenuActive = menu.submenu.some(item => {
        const itemFileName = item.link.split('/').pop().toLowerCase();
        return itemFileName === currentFileName;
      });

      const isOpen = isSubmenuActive || index === activeIndex;

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
      const isMainActive = index === activeIndex;

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
        C Tutorial
      </h5>
      <nav class="flex flex-col gap-3">
        ${menuHTML}
      </nav>
    </aside>
  `;
}

// Submenu Dropdown Open/Close Event Handler
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