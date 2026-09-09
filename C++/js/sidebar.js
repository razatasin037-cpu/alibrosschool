function createSidebar(title = "C++ Tutorial", menus = null) {
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
      .submenu-wrapper {
        transition: max-height 0.4s ease-in-out, opacity 0.35s ease-in-out, margin 0.3s ease;
        max-height: 0;
        opacity: 0;
        overflow: hidden;
      }
      .submenu-wrapper.open {
        max-height: 1000px;
        opacity: 1;
      }
    `;
    document.head.appendChild(style);
  }

  const defaultMenus = [
    { name: "C++ HOME", link: "/C++/home/c++tutoreal.html" },
    { name: "C++ Intro", link: "/C++/intro/intro.html" },
    { name: "C++ Get Started", link: "/C++/getstart/start.html" },
    {
      name: "C++ Syntax",
      link: "/C++/syntax/syntax.html",
      submenu: [
        { name: "Syntax", link: "/C++/syntax/syntax.html" },
        { name: "Statements", link: "/C++/syntax/statment.html" },
      ],
    },
    {
      name: "C++ Output",
      link: "/C++/output/text.html",
      submenu: [
        { name: "Print Text", link: "/C++/output/text.html" },
        { name: "New Lines", link: "/C++/output/new.html" },
        { name: "Print Numbers", link: "/C++/output/print.html" },
      ],
    },
    { name: "C++ Comments", link: "/C++/comments/comments.html" },
    {
      name: "C++ Variables",
      link: "/C++/varibale/declarevariables.html",
      submenu: [
        {
          name: "Declare Variables",
          link: "/C++/varibale/declarevariables.html",
        },
        {
          name: "Multiple Variables",
          link: "/C++/varibale/multiplevariables.html",
        },
        { name: "Identifier", link: "/C++/varibale/identifiers.html" },
        { name: "Constants", link: "/C++/varibale/constants.html" },
        {
          name: "Real Life Example",
          link: "/C++/varibale/real-life-example.html",
        },
      ],
    },
    { name: "C++ User Input", link: "/C++/userinput/user-input.html" },
    {
      name: "C++ Data Types",
      link: "/C++/datatype/datatypes.html",
      submenu: [
        { name: "Data Types", link: "/C++/datatype/datatypes.html" },
        { name: "Numeric Data Types", link: "/C++/datatype/numerictypes.html" },
        { name: "Boolean Data Type", link: "/C++/datatype/booleanss.html" },
        { name: "Character Data Type", link: "/C++/datatype/characters.html" },
        { name: "String Data Type", link: "/C++/datatype/stringsdata.html" },
        { name: "The auto Keyword", link: "/C++/datatype/auto.html" },
        {
          name: "Real Life Example",
          link: "/C++/datatype/real-life-example-datatypes.html",
        },
      ],
    },
    {
      name: "C++ Operators",
      link: "/C++/opretor/operators.html",
      submenu: [
        { name: "Operators", link: "/C++/opretor/operators.html" },
        {
          name: "Arithmetic Operators",
          link: "/C++/opretor/arithmeticoperators.html",
        },
        {
          name: "Assignment Operators",
          link: "/C++/opretor/assignmentoperators.html",
        },
        {
          name: "Comparison Operators",
          link: "/C++/opretor/comparisonoperators.html",
        },
        {
          name: "Logical Operators",
          link: "/C++/opretor/logicaloperators.html",
        },
        { name: "Precedence", link: "/C++/opretor/precedence.html" },
      ],
    },
    {
      name: "C++ Strings",
      link: "/C++/string/string.html",
      submenu: [
        { name: "Strings", link: "/C++/string/string.html" },
        {
          name: "String Concatenation",
          link: "/C++/string/string-concatenation.html",
        },
        {
          name: "Numbers and Strings",
          link: "/C++/string/numbers-strings.html",
        },
        { name: "String Length", link: "/C++/string/string-length.html" },
        { name: "Access Strings", link: "/C++/string/access-strings.html" },
        {
          name: "Special Characters",
          link: "/C++/string/special-characters.html",
        },
        {
          name: "User Input Strings",
          link: "/C++/string/user-input-strings.html",
        },
        { name: "C-Style Strings", link: "/C++/string/cstyle.html" },
      ],
    },
    { name: "C++ Math", link: "/C++/math/maths.html" },
    { name: "C++ Booleans", link: "/C++/boolenss/booleanss.html" },
    {
      name: "C++ If...Else",
      link: "/C++/if/conditions.html",
      submenu: [
        { name: "Conditions", link: "/C++/if/conditions.html" },
        { name: "else Statement", link: "/C++/if/else.html" },
        { name: "else if", link: "/C++/if/elseif.html" },
        { name: "Short Hand if...else", link: "/C++/if/short-if-else.html" },
        { name: "Nested if", link: "/C++/if/nested-if.html" },
        { name: "Logical Operators", link: "/C++/if/logical-operators.html" },
        {
          name: "Real-Life Examples",
          link: "/C++/if/real-life-examplesif.html",
        },
      ],
    },
    { name: "C++ Switch", link: "/C++/switch/switch.html" },
    {
      name: "C++ While Loop",
      link: "/C++/while/while-loop.html",
      submenu: [
        { name: "While Loop", link: "/C++/while/while-loop.html" },
        { name: "Do While Loop", link: "/C++/while/do-while.html" },
      ],
    },
    {
      name: "C++ For Loop",
      link: "/C++/for/for-loop.html",
      submenu: [
        { name: "For Loop", link: "/C++/for/for-loop.html" },
        { name: "Nested Loops", link: "/C++/for/nested-loops.html" },
        { name: "The For Each Loop ", link: "/C++/for/foreach-loop.html" },
        { name: "Real Life Example", link: "/C++/for/real-life-examples.html" },
      ],
    },
    { name: "C++ Break / Continue", link: "/C++/break/break-continue.html" },
    {
      name: "C++ Arrays",
      link: "/C++/arrays/arrays.html",
      submenu: [
        { name: "Arrays", link: "/C++/arrays/arrays.html" },
        { name: "Access Arrays", link: "/C++/arrays/access-arrays.html" },
        {
          name: "Change Array Elements",
          link: "/C++/arrays/change-array.html",
        },
        { name: "Array Length", link: "/C++/arrays/array-length.html" },
        { name: "Loop Through Array", link: "/C++/arrays/loop-array.html" },
        {
          name: "Multidimensional Arrays",
          link: "/C++/arrays/multi-dimensional-array.html",
        },
      ],
    },
    { name: "C++ Structures", link: "/C++/structures/structures.html" },
    { name: "C++ Enums", link: "/C++/enums/enums.html" },

    {
      name: "C++ Pointers",
      link: "/C++/pointers/pointers.html",
      submenu: [
        { name: "Pointers", link: "/C++/pointers/pointers.html" },
        {
          name: "Pointer Operators",
          link: "/C++/pointers/pointer-operators.html",
        },
        {
          name: "Pointers and Arrays",
          link: "/C++/pointers/pointers-arrays.html",
        },
      ],
    },
    {
      name: "C++ Functions",
      link: "/C++/functions/functions.html",
      submenu: [
        {
          name: "Functions",
          link: "/C++/functions/functions.html",
        },
        {
          name: "Parameters/Arguments",
          link: "/C++/functions/parameters.html",
        },
        {
          name: "Default Parameter",
          link: "/C++/functions/default-parameter.html",
        },
        {
          name: "Multiple Parameters",
          link: "/C++/functions/multiple-parameters.html",
        },
        {
          name: "Return Values",
          link: "/C++/functions/return-values.html",
        },
        {
          name: "Pass By Reference",
          link: "/C++/functions/pass-by-reference.html",
        },
        {
          name: "Pass Arrays",
          link: "/C++/functions/pass-arrays.html",
        },
        {
          name: "Pass Structures",
          link: "/C++/functions/pass-structures.html",
        },
        {
          name: "Real-Life Example",
          link: "/C++/functions/real-life-example.html",
        },
        {
          name: "Function Overloading",
          link: "/C++/functions/function-overloading.html",
        },
        {
          name: "Recursion",
          link: "/C++/functions/recursion.html",
        },
      ],
    },
    {
      name: "C++ OOP",
      link: "/C++/oop/oop.html",
      submenu: [
        {
          name: "C++ OOP",
          link: "/C++/oop/oop.html",
        },
        {
          name: "C++ Classes/Objects",
          link: "/C++/oop/classes.html",
        },
        {
          name: "C++ Class Methods",
          link: "/C++/oop/class-methods.html",
        },

        {
          name: "C++ Constructors",
          link: "/C++/oop/constructors.html",
        },
        {
          name: "C++ Constructor Overloading",
          link: "/C++/oop/constructor-overloading.html",
        },
        {
          name: "C++ Access Specifiers",
          link: "/C++/oop/access-specifiers.html",
        },
        {
          name: "C++ Encapsulation",
          link: "/C++/oop/encapsulation.html",
        },
        {
          name: "C++ Friend Functions",
          link: "/C++/oop/friend-functions.html",
        },
        {
          name: "C++ Inheritance",
          link: "/C++/oop/inheritance.html",
        },

        {
          name: "Multilevel Inheritance",
          link: "/C++/oop/multilevel-inheritance.html",
        },
        {
          name: "Multiple Inheritance",
          link: "/C++/oop/multiple-inheritance.html",
        },
        {
          name: "Inheritance Access Specifiers",
          link: "/C++/oop/inheritance-access-specifiers.html",
        },
        {
          name: "C++ Polymorphism",
          link: "/C++/oop/polymorphism.html",
        },
        {
          name: "Virtual Functions",
          link: "/C++/oop/virtual-functions.html",
        },
      ],
    },
  ];

  const menuList = menus || defaultMenus;
  const currentPath = window.location.pathname.toLowerCase();

  let menuHTML = "";

  menuList.forEach((menu) => {
    if (menu.submenu) {
      const isOpen =
        currentPath.endsWith(menu.link.toLowerCase()) ||
        menu.submenu.some((item) =>
          currentPath.endsWith(item.link.toLowerCase()),
        );

      menuHTML += `
        <div>
          <div class="group flex items-center rounded-[18px] overflow-hidden ${
            isOpen ? "bg-white/10" : "hover:bg-[#3d5dff]"
          } transition duration-200">
            <a href="${menu.link}" class="sidebar-link flex-1 py-[18px] px-[35px] text-white text-[18px] font-bold">
              ${menu.name}
            </a>
            <button type="button" class="submenu-btn px-5 py-[18px] text-white focus:outline-none">
              <i class="fa-solid ${isOpen ? "fa-caret-down" : "fa-caret-right"}"></i>
            </button>
          </div>
          <div class="submenu-wrapper ${isOpen ? "open mt-2" : ""} ml-8 flex flex-col gap-2">
      `;

      menu.submenu.forEach((item) => {
        const active = currentPath.endsWith(item.link.toLowerCase());

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
      const active = currentPath.endsWith(menu.link.toLowerCase());

      menuHTML += `
        <a href="${menu.link}" class="sidebar-link block py-[18px] px-[35px] text-white text-[18px] font-bold rounded-[18px] ${
          active ? "bg-[#3d5dff]" : "hover:bg-[#3d5dff]"
        } transition">
          ${menu.name}
        </a>
      `;
    }
  });

  return `
    <aside id="sidebar-container" class="hidden md:block sticky top-[110px] w-[300px] h-[calc(100vh-110px)] overflow-y-auto custom-scrollbar bg-[#020b24] p-5">
      
      <nav class="flex flex-col gap-3">
        ${menuHTML}
      </nav>
    </aside>
  `;
}

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

document.addEventListener("click", function (e) {
  const link = e.target.closest(".sidebar-link");
  if (!link) return;

  const sidebar = document.getElementById("sidebar-container");
  if (sidebar) {
    sessionStorage.setItem("sidebarScrollTop", sidebar.scrollTop);
  }
});

window.addEventListener("DOMContentLoaded", function () {
  const savedScroll = sessionStorage.getItem("sidebarScrollTop");
  const sidebar = document.getElementById("sidebar-container");

  if (sidebar && savedScroll !== null) {
    sidebar.scrollTop = parseInt(savedScroll, 10);
  }
});
