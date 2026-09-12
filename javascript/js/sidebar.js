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
    { name: "JS HOME", link: "/javascript/home/home.html" },
    { name: "JS Intro", link: "/javascript/intro/Intro.html" },
    { name: "JS Where To", link: "/javascript/whereTo/WhereTo.html" },
    { name: "JS Output", link: "/javascript/output/Output.html" },
    // syntax
    {
      name: "JS Syntax",
      link: "/javascript/syntax/Syntax.html",
      submenu: [
        { name: "JS Syntax", link: "/javascript/syntax/Syntax.html", },
        { name: "JS Statements", link: "/javascript/syntax/Statements.html" },
        { name: "JS Comments ", link: "/javascript/syntax/Comments.html" },
        { name: "JS Variables ", link: "/javascript/syntax/Variables.html" },
        { name: "JS Let ", link: "/javascript/syntax/Let.html" },
        { name: "JS Const ", link: "/javascript/syntax/Const.html" },
        { name: "JS Types ", link: "/javascript/syntax/Types.html" }
      ]
    },
    //  Operators
    {
      name: "JS Operators",
      link: "/javascript/operators/Operators.html",
      submenu: [
        { name: "JS Operators", link: "/javascript/operators/Operators.html", },
        { name: "JS Arithmetic", link: "/javascript/operators/Arithmetic.html" },
        { name: "JS Assignment ", link: "/javascript/operators/Assignment.html" },
        { name: "JS Comparisons", link: "/javascript/operators/Comparisons.html" },
        { name: "JS Conditionals ", link: "/javascript/operators/Conditionals.html" }
      ]
    },

    // Conditions

    {
      name: "JS If Conditions",
      link: "/javascript/ifconditions/If.html",
      submenu: [
        { name: "JS If", link: "/javascript/ifconditions/If.html" },
        { name: "JS If Else  ", link: "/javascript/ifconditions/IfElse.html" },
        { name: "JS Ternary", link: "/javascript/ifconditions/Ternary.html" },
        { name: "JS Switch ", link: "/javascript/ifconditions/Switch.html" },
        { name: "JS Booleans", link: "/javascript/ifconditions/Booleans.html" },
        { name: "JS Logical", link: "/javascript/ifconditions/Logical.html" }
      ]
    },

    // Loops
    {
      name: "JS Loops",
      link: "/javascript/loops/Loops.html",
      submenu: [
        { name: "JS Loops", link: "/javascript/loops/Loops.html", },
        { name: "JS Loop For", link: "/javascript/loops/LoopFor.html" },
        { name: "JS Loop While", link: "/javascript/loops/LoopWhile.html" },
        { name: "JS Break ", link: "/javascript/loops/Break.html" },
        { name: "JS Continue", link: "/javascript/loops/Continue.html" }
      ]
    },

    // Strings
    {
      name: "JS Strings",
      link: "/javascript/strings/Strings.html",
      submenu: [
        { name: "JS Strings", link: "/javascript/strings/Strings.html", },
        { name: "JS Strings Templates", link: "/javascript/strings/StringsTemplates.html" },
        { name: "JS Strings Methods", link: "/javascript/strings/StringsMethods.html" },
        { name: "JS Strings Search", link: "/javascript/strings/Search.html" },
        { name: "JS Strings Reference", link: "/javascript/strings/StringsReference.html" }
      ]
    },

    // Numbers
    {
      name: "JS Numbers",
      link: "/javascript/numbers/Numbers.html",
      submenu: [
        { name: "JS Numbers", link: "/javascript/numbers/Numbers.html", },
        { name: "JS Numbers Methods", link: "/javascript/numbers/NumbersMethods.html" },
        { name: "JS Numbers Properties", link: "/javascript/numbers/Properties.html" },
        { name: "JS Numbers Reference", link: "/javascript/numbers/NumbersReference.html" },
        { name: "JS Bitwise", link: "/javascript/numbers/Bitwise.html" },
        { name: "JS BigInt", link: "/javascript/numbers/BigInt.html" }
      ]
    },

    // Functions
    {
      name: "JS Functions",
      link: "/javascript/functions/Functions.html",
      submenu: [
        { name: "JS Functions", link: "/javascript/functions/Functions.html", },
        { name: "JS Functions Parameters", link: "/javascript/functions/Parameters.html" },
        { name: "JS Functions Return", link: "/javascript/functions/Return.html" },
        { name: "JS Functions Arguments", link: "/javascript/functions/Arguments.html" },
        { name: "JS Function Expression", link: "/javascript/functions/FunctionExpression.html" },
        { name: "JS Functions Arrow ", link: "/javascript/functions/ArrowFunction.html" }
      ]
    },
    // Timers
    { name: "JS Timers ", link: "/javascript/timers/Timers.html" },

    // Objects
    {
      name: "JS Objects",
      link: "/javascript/objects/Objects.html",

      submenu: [
        {
          name: "JS Objects",
          link: "/javascript/objects/Objects.html"
        },
        {
          name: "JS Object Properties",
          link: "/javascript/objects/ObjectProperties.html"
        },
        {
          name: "JS Object Methods",
          link: "/javascript/objects/ObjectMethods.html"
        },
        {
          name: "JS Object Display",
          link: "/javascript/objects/Display.html"
        },
        {
          name: "JS Object Constructors",
          link: "/javascript/objects/Constructors.html"
        }
      ]
    },

    // Scope
    {
      name: "JS Scope",
      link: "/javascript/scope/Scope.html",

      submenu: [
        {
          name: "JS Scope",
          link: "/javascript/scope/Scope.html"
        },
        {
          name: "JS Block Scope",
          link: "/javascript/scope/BlockScope.html"
        },
        {
          name: "JS Function Scope",
          link: "/javascript/scope/FunctionScope.html"
        },
        {
          name: "JS Global Scope",
          link: "/javascript/scope/GlobalScope.html"
        }
      ]
    },

    // Dates
    {
      name: "JS Dates",
      link: "/javascript/dates/Dates.html",

      submenu: [
        {
          name: "JS Dates",
          link: "/javascript/dates/Dates.html"
        },
        {
          name: "JS Date Formats",
          link: "/javascript/dates/Formats.html"
        },
        {
          name: "JS Date Get Methods",
          link: "/javascript/dates/GetMethods.html"
        },
        {
          name: "JS Date Set Methods",
          link: "/javascript/dates/SetMethods.html"
        }
      ]
    },

    // Arrays
    {
      name: "JS Arrays",
      link: "/javascript/arrays/Arrays.html",

      submenu: [
        {
          name: "JS Arrays",
          link: "/javascript/arrays/Arrays.html"
        },
        {
          name: "JS Array Methods",
          link: "/javascript/arrays/ArrayMethods.html"
        },
        {
          name: "JS Array Search",
          link: "/javascript/arrays/ArraysSearch.html"
        },
        {
          name: "JS Array Sort",
          link: "/javascript/arrays/Sort.html"
        },
        {
          name: "JS Array Iteration",
          link: "/javascript/arrays/Iteration.html"
        },
        {
          name: "JS Array Const",
          link: "/javascript/arrays/Const.html"
        }
      ]
    },

    // Sets
    {
      name: "JS Sets",
      link: "/javascript/sets/Sets.html",

      submenu: [
        {
          name: "JS Sets",
          link: "/javascript/sets/Sets.html"
        },
        {
          name: "JS Set Methods",
          link: "/javascript/sets/SetMethod.html"
        }
       
      ]
    },

    // Maps
    {
      name: "JS Maps",
      link: "/javascript/maps/Maps.html",

      submenu: [
        {
          name: "JS Maps",
          link: "/javascript/maps/Maps.html"
        },
        {
          name: "JS Map Methods",
          link: "/javascript/maps/MapMethods.html"
        }
       
      ]
    },

    // Iterations
    {
      name: "JS Iterations",
      link: "/javascript/iterations/Iterations.html",

      submenu: [
        {
          name: "JS Iterations",
          link: "/javascript/iterations/Iterations.html"
        },
        {
          name: "JS forEach",
          link: "/javascript/iterations/ForEach.html"
        },
        {
          name: "JS map",
          link: "/javascript/iterations/Map.html"
        },
        {
          name: "JS filter",
          link: "/javascript/iterations/Filter.html"
        },
        {
          name: "JS reduce",
          link: "/javascript/iterations/Reduce.html"
        },
        {
          name: "JS for...of",
          link: "/javascript/iterations/ForOf.html"
        }
      ]
    },

    // Math
    {
      name: "JS Math",
      link: "/javascript/math/Math.html",

      submenu: [
        {
          name: "JS Math",
          link: "/javascript/math/Math.html"
        },
        {
          name: "JS Math Methods",
          link: "/javascript/math/MathMethods.html"
        },
        {
          name: "JS Math Properties",
          link: "/javascript/math/MathProperties.html"
        },
        {
          name: "JS Math Random",
          link: "/javascript/math/Random.html"
        }
    
      ]
    },

    // Data Types
    {
      name: "JS Data Types",
      link: "/javascript/datatypes/DataTypes.html",

      submenu: [
        {
          name: "JS Data Types",
          link: "/javascript/datatypes/DataTypes.html"
        },
        {
          name: "JS typeof",
          link: "/javascript/datatypes/Typeof.html"
        },
        {
          name: "JS Type Conversion",
          link: "/javascript/datatypes/Conversion.html"
        },
        {
          name: "JS Destructuring",
          link: "/javascript/datatypes/Destructuring.html"
        }
      ]
    },

    // Errors
    {
      name: "JS Errors",
      link: "/javascript/errors/Errors.html",

      submenu: [
        {
          name: "JS Errors",
          link: "/javascript/errors/Errors.html"
        },
        {
          name: "JS Try Catch",
          link: "/javascript/errors/TryCatch.html"
        },
        {
          name: "JS Throw",
          link: "/javascript/errors/Throw.html"
        },
        {
          name: "JS Finally",
          link: "/javascript/errors/Finally.html"
        }
      ]
    },

    // Debugging
    {
      name: "JS Debugging",
      link: "/javascript/debugging/Debugging.html"
    },

    // JSON
    {
      name: "JS JSON",
      link: "/javascript/json/JSON.html",

      submenu: [
        {
          name: "JS JSON",
          link: "/javascript/json/JSON.html"
        },
        {
          name: "JSON Parse",
          link: "/javascript/json/Parse.html"
        },
        {
          name: "JSON Stringify",
          link: "/javascript/json/Stringify.html"
        }
      ]
    },

    // Classes
    {
      name: "JS Classes",
      link: "/javascript/classes/Classes.html",

      submenu: [
        {
          name: "JS Classes",
          link: "/javascript/classes/Classes.html"
        },
        {
          name: "JS Class Constructor",
          link: "/javascript/classes/Constructor.html"
        },
        {
          name: "JS Class Methods",
          link: "/javascript/classes/ClassesMethods.html"
        },
        {
          name: "JS Class Inheritance",
          link: "/javascript/classes/Inheritance.html"
        },
        {
          name: "JS Static Methods",
          link: "/javascript/classes/Static.html"
        }
      ]
    },

    // Modules
    {
      name: "JS Modules",
      link: "/javascript/modules/Modules.html",

      submenu: [
        {
          name: "JS Modules",
          link: "/javascript/modules/Modules.html"
        },
        {
          name: "JS Export",
          link: "/javascript/modules/Export.html"
        },
        {
          name: "JS Import",
          link: "/javascript/modules/Import.html"
        }
      ]
    },

    // Async JavaScript
    {
      name: "JS Async",
      link: "/javascript/async/Async.html",

      submenu: [
        {
          name: "JS Async",
          link: "/javascript/async/Async.html"
        },
        {
          name: "JS Callbacks",
          link: "/javascript/async/Callbacks.html"
        },
        {
          name: "JS Promises",
          link: "/javascript/async/Promises.html"
        },
        {
          name: "JS Async Await",
          link: "/javascript/async/AsyncAwait.html"
        },
        {
          name: "JS Fetch API",
          link: "/javascript/async/Fetch.html"
        }
      ]
    },

    // HTML DOM
    {
      name: "JS HTML DOM",
      link: "/javascript/dom/DOM.html",

      submenu: [
        {
          name: "JS DOM",
          link: "/javascript/dom/DOM.html"
        },
        {
          name: "JS DOM Elements",
          link: "/javascript/dom/Elements.html"
        },
        {
          name: "JS DOM HTML",
          link: "/javascript/dom/HTML.html"
        },
        {
          name: "JS DOM CSS",
          link: "/javascript/dom/CSS.html"
        },
        {
          name: "JS DOM Events",
          link: "/javascript/dom/Events.html"
        },
        {
          name: "JS DOM Forms",
          link: "/javascript/dom/Forms.html"
        },
        {
          name: "JS DOM Navigation",
          link: "/javascript/dom/Navigation.html"
        },
        {
          name: "JS DOM Nodes",
          link: "/javascript/dom/Nodes.html"
        }
      ]
    },

    // Browser
    {
      name: "JS Browser",
      link: "/javascript/browser/Browser.html",

      submenu: [
         {
          name: "JS Browser",
          link: "/javascript/browser/Browser.html"
        },
        {
          name: "JS Window",
          link: "/javascript/browser/Window.html"
        },
        {
          name: "JS Screen",
          link: "/javascript/browser/Screen.html"
        },
        {
          name: "JS Location",
          link: "/javascript/browser/Location.html"
        },
        {
          name: "JS History",
          link: "/javascript/browser/History.html"
        },
        {
          name: "JS Navigator",
          link: "/javascript/browser/Navigator.html"
        },
        {
          name: "JS Storage",
          link: "/javascript/browser/Storage.html"
        }
      ]
    },


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
          <div class="group flex items-center rounded-[18px] overflow-hidden ${isOpen ? "bg-white/10" : "hover:bg-[#3d5dff]"
        } transition duration-200">

            <a href="${menu.link}" class="sidebar-link flex-1 py-[18px] px-[35px] text-white text-[18px] font-bold">
              ${menu.name}
            </a>

            <button type="button" class="submenu-btn px-5 py-[18px] text-white focus:outline-none">
              <i class="fa-solid ${isOpen ? "fa-caret-down" : "fa-caret-right"
        }"></i>
            </button>

          </div>

          <div class="submenu-wrapper ${isOpen ? "open mt-2" : ""
        } ml-8 flex flex-col gap-2">
      `;

      menu.submenu.forEach(item => {
        const itemFileName = item.link.split('/').pop().toLowerCase();
        const active = currentFileName === itemFileName;

        menuHTML += `
          <a href="${item.link}" class="sidebar-link block px-4 py-2 rounded-lg font-bold transition ${active
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
        <a href="${menu.link}" class="sidebar-link block py-[18px] px-[35px] text-white text-[18px] font-bold rounded-[18px] ${isMainActive ? "bg-[#3d5dff]" : "hover:bg-[#3d5dff]"
        } transition">
          ${menu.name}
        </a>
      `;
    }
  });

  return `
    <aside id="sidebar-container" class="hidden md:block sticky top-[110px] w-[300px] h-[calc(100vh-110px)] overflow-y-auto custom-scrollbar bg-[#020b24] p-5">
      <h5 class="text-[#7f8db4] tracking-[5px] text-[20px] font-bold mb-5 ml-6 uppercase">
          JS TUTORIAL
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