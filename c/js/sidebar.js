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
        { name: "Syntax", link: "/c/syntax/syntax.html" },
        { name: "Statements", link: "/c/syntax/statements.html" },
        { name: "Syntax Code Challenge", link: "/c/syntax/syntaxCodeChallenge.html" }
      ]
    },
    {
      name: "C Output",
      link: "/c/output/printText.html",
      submenu: [
        { name: "Print Text", link: "/c/output/printText.html" },
        { name: "New Lines", link: "/c/output/newLines.html" }
      ]
    },
    { name: "C Comments", link: "/c/comments/comments.html" },
    {
      name: "C Variables",
      link: "/c/Variables/createVariables.html",
      submenu: [
        { name: "Create Variables", link: "/c/Variables/createVariables.html" },
        { name: "Format Specifiers", link: "/c/Variables/formatSpecifiers.html" },
        { name: "Change Variable", link: "/c/Variables/ChangeVariable.html" },
        { name: "Multiple Variable", link: "/c/Variables/MultipleVariable.html" },
        { name: "Variable Names", link: "/c/Variables/variableNames.html" },
        { name: "Real-Life Examples", link: "/c/Variables/Real-Life-Examples.html" }
      ]
    },
    { name: "C User Input", link: "/c/input/UserInput.html" },
    {
      name: "C Data Types",
      link: "/c/datatypes/data-types.html",
      submenu: [
        { name: "Data Types", link: "/c/datatypes/data-types.html" },
        { name: "Characters", link: "/c/datatypes/Characterdatatype.html" },
        { name: "Numbers ", link: "/c/datatypes/NumericDataType.html" },
        { name: "Decimal Precision", link: "/c/datatypes/DecimalDataType.html" },
        { name: "Memory Size", link: "/c/datatypes/MemoryOfSize.html" },
        { name: "Real-Life Examples", link: "/c/datatypes/Real-LifeDataTypesExamples.html" },
        { name: "Extended Types", link: "/c/datatypes/ExtendedTypes.html" }
      ]
    },
    { name: "C Type Conversion", link: "/c/typeConversion/TypeConversion.html" },
    { name: "C Constants", link: "/c/Constants/Constants.html" },
    { name: "C Booleans", link: "/c/booleans/Booleans.html" },
    {
      name: "C Operators",
      link: "/c/operators/operators.html",
      submenu: [
        { name: "Operators", link: "/c/operators/operators.html" },
        { name: "Arithmetic ", link: "/c/operators/Arithmetic.html" },
        { name: "Assignment ", link: "/c/operators/Assignment.html" },
        { name: "Comparison  ", link: "/c/operators/Comparison.html" },
        { name: "Logical ", link: "/c/operators/Logical.html" },
        { name: "Precedence", link: "/c/operators/Precedence.html" }
      ]
    },
    {
      name: "C If...Else",
      link: "/c/ifElse/If.html",
      submenu: [
        { name: "If", link: "/c/ifElse/If.html" },
        { name: "Else ", link: "/c/ifElse/Else.html" },
        { name: "Else If ", link: "/c/ifElse/ElseIf.html" },
        { name: "Short Hand If  ", link: "/c/ifElse/ShortHandIf.html" },
        { name: "Nested If ", link: "/c/ifElse/NestedIf.html" },
        { name: "Real-Life Examples", link: "/c/ifElse/Real-Life-Example.html" }
      ]
    },
    { name: "C Switch", link: "/c/switch/Switch.html"},
    {
      name: "C Loop",
      link: "/c/loop/WhileLoop.html",
      submenu: [
        { name: "While Loop", link: "/c/loop/WhileLoop.html" },
        { name: "Do-While Loop", link: "/c/loop/DoWhileLoop.html" },
        { name: "For Loop", link: "/c/loop/ForLoop.html" }
      ]
    },
     {
      name: "C Break/Continue",link: "/c/BreakandContinue/BreakandContinue.html",
    },
     {
      name: "C Arrays",
      link: "/c/arrays/Arrays.html",
      submenu: [
        { name: "Arrays ", link: "/c/arrays/Arrays.html" },
        { name: "Arrays Size", link: "/c/arrays/ArraysSizes.html" },
        { name: "Arrays Loops", link: "/c/arrays/ArraysLoops.html" }
      ]
    },
    {
      name: "C Strings",
      link: "/c/strings/Strings.html",
      submenu: [
        { name: "Strings ", link: "/c/strings/Strings.html" },
        { name: "Special Characters", link: "/c/strings/SpecialCharacters.html" },
        { name: "String Functions ", link: "/c/strings/StringFunctions.html" }
      ]
    },

     {
      name: "C Pointers",
      link: "/c/pointers/Pointers.html",
      submenu: [
        { name: "Pointers ", link: "/c/pointers/Pointers.html" },
        { name: "Pointers & Arrays", link: "/c/pointers/PointersandArrays.html" },
        { name: "Pointer Arithmetic ", link: "/c/pointers/PointerArithmetic.html" },
        {name: "Pointer to Pointer", link: "/c/pointers/PointertoPointer.html"}
      ]
    },
    {
      name: "C Functions",
      link: "/c/functions/Functions.html",
      submenu: [
        { name: "Functions", link: "/c/functions/Functions.html" },
        { name: "Function Parameters", link: "/c/functions/FunctionParameters.html" },
        { name: "Function Declaration", link: "/c/functions/FunctionDeclaration.html" },
        { name: "Recursion", link: "/c/functions/Recursion.html" }
      ]
    },
    {
      name: "C Structures",
      link: "/c/structures/Structures.html",
      submenu: [
        { name: "Structures", link: "/c/structures/Structures.html" },
        { name: "Nested Structures", link: "/c/structures/NestedStructures.html" },
        { name: "Structures & Strings", link: "/c/structures/StructuresStrings.html" },
        { name: "Real-Life Examples", link: "/c/structures/RealLifeExamples.html" }
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