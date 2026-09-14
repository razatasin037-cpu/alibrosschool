function createSidebar(activeIndex = 0) {
  /* =========================
     SIDEBAR CSS
  ========================== */

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

      #sidebar-container {
        border: none !important;
        border-right: none !important;
        box-shadow: none !important;
        outline: none !important;
      }

      #sidebar-container * {
        border-right: none;
      }

      .sidebar-link {
        text-decoration: none;
      }

      .submenu-wrapper {
        display: none;
      }

      .submenu-wrapper.open {
        display: flex;
      }

    `;

    document.head.appendChild(style);
  }

  /* =========================
     SQL MENU
  ========================== */

  const menus = [
    {
      name: "SQL HOME",
      link: "/SQL/home/sql-tutorial.html",
    },

    {
      name: "SQL Introduction",
      link: "/SQL/introduction/introduction.html",
    },

    {
      name: "SQL Syntax",
      link: "/SQL/syntax/syntax.html",
    },

    {
      name: "SQL SELECT",
      link: "/SQL/select/select.html",
    },

    {
      name: "SQL SELECT DISTINCT",
      link: "/SQL/select-distinct/selectdistinct.html",
    },

    {
      name: "SQL WHERE",
      link: "/SQL/where/where.html",
    },

    {
      name: "SQL AND / OR / NOT",
      link: "/SQL/and-or-not/andornot.html",
    },

    {
      name: "SQL ORDER BY",
      link: "/SQL/order-by/orderby.html",
    },

    {
      name: "SQL INSERT INTO",
      link: "/SQL/insert-into/insertinto.html",
    },

    {
      name: "SQL NULL Values",
      link: "/SQL/null-values/nullvalues.html",
    },

    {
      name: "SQL UPDATE",
      link: "/SQL/update/update.html",
    },

    {
      name: "SQL DELETE",
      link: "/SQL/delete/delete.html",
    },

    {
      name: "SQL LIMIT",
      link: "/SQL/limit/limit.html",
    },

    {
      name: "SQL MIN / MAX",
      link: "/SQL/min-max/minmax.html",
    },

    {
      name: "SQL COUNT / AVG / SUM",
      link: "/SQL/count-avg-sum/countavgsum.html",
    },

    {
      name: "SQL LIKE",
      link: "/SQL/like/like.html",
    },

    {
      name: "SQL Wildcards",
      link: "/SQL/wildcards/wildcards.html",
    },

    {
      name: "SQL IN",
      link: "/SQL/in/in.html",
    },

    {
      name: "SQL BETWEEN",
      link: "/SQL/between/between.html",
    },

    {
      name: "SQL Aliases",
      link: "/SQL/aliases/aliases.html",
    },

    /* =========================
       JOIN
    ========================== */

    {
      name: "SQL JOIN",
      link: "/SQL/join/join.html",

      submenu: [
        {
          name: "JOIN",
          link: "/SQL/join/join.html",
        },

        {
          name: "INNER JOIN",
          link: "/SQL/inner-join/innerjoin.html",
        },

        {
          name: "LEFT JOIN",
          link: "/SQL/left-join/leftjoin.html",
        },

        {
          name: "RIGHT JOIN",
          link: "/SQL/right-join/rightjoin.html",
        },

        {
          name: "FULL JOIN",
          link: "/SQL/full-join/fulljoin.html",
        },
      ],
    },

    {
      name: "SQL UNION",
      link: "/SQL/union/union.html",
    },

    {
      name: "SQL GROUP BY",
      link: "/SQL/group-by/groupby.html",
    },

    {
      name: "SQL HAVING",
      link: "/SQL/having/having.html",
    },

    {
      name: "SQL EXISTS",
      link: "/SQL/exists/exists.html",
    },

    {
      name: "SQL CASE",
      link: "/SQL/case/case.html",
    },

    /* =========================
       CREATE
    ========================== */

    {
      name: "SQL CREATE",
      link: "/SQL/create-database/createdatabase.html",

      submenu: [
        {
          name: "CREATE DATABASE",
          link: "/SQL/create-database/createdatabase.html",
        },

        {
          name: "CREATE TABLE",
          link: "/SQL/create-table/createtable.html",
        },
      ],
    },

    {
      name: "SQL ALTER TABLE",
      link: "/SQL/alter-table/altertable.html",
    },

    {
      name: "SQL DROP TABLE",
      link: "/SQL/drop-table/droptable.html",
    },

    {
      name: "SQL Constraints",
      link: "/SQL/constraints/constraints.html",
    },

    /* =========================
       KEYS
    ========================== */

    {
      name: "SQL Keys",
      link: "/SQL/primary-key/primarykey.html",

      submenu: [
        {
          name: "PRIMARY KEY",
          link: "/SQL/primary-key/primarykey.html",
        },

        {
          name: "FOREIGN KEY",
          link: "/SQL/foreign-key/foreignkey.html",
        },
      ],
    },

    {
      name: "SQL Index",
      link: "/SQL/index/index.html",
    },

    {
      name: "SQL Views",
      link: "/SQL/views/views.html",
    },

    {
      name: "SQL Injection",
      link: "/SQL/sql-injection/sqlinjection.html",
    },
  ];

  /* =========================
     CURRENT PAGE
  ========================== */

  const currentPath = window.location.pathname.toLowerCase();

  let menuHTML = "";

  /* =========================
     CREATE MENU HTML
  ========================== */

  menus.forEach((menu, index) => {
    /* =========================
       MENU WITH SUBMENU
    ========================== */

    if (menu.submenu) {
      const isOpen =
        currentPath.endsWith(menu.link.toLowerCase()) ||
        menu.submenu.some((item) =>
          currentPath.endsWith(item.link.toLowerCase()),
        );

      menuHTML += `

        <div>

          <div
            class="group flex items-center rounded-[18px] overflow-hidden
            ${isOpen ? "bg-white/10" : "hover:bg-[#3d5dff]"}
            transition duration-200"
          >

            <a
              href="${menu.link}"
              class="sidebar-link flex-1 py-[18px] px-[35px]
              text-white text-[18px] font-bold"
            >
              ${menu.name}
            </a>


            <button
              type="button"
              class="submenu-btn px-5 py-[18px]
              text-white focus:outline-none"
              aria-label="Toggle submenu"
            >

              <i
                class="fa-solid ${isOpen ? "fa-caret-down" : "fa-caret-right"}"
              ></i>

            </button>

          </div>


          <div
            class="submenu-wrapper
            ${isOpen ? "open mt-2" : ""}
            ml-8 flex-col gap-2"
          >
      `;

      /* =========================
         SUBMENU ITEMS
      ========================== */

      menu.submenu.forEach((item) => {
        const active = currentPath.endsWith(item.link.toLowerCase());

        menuHTML += `

          <a
            href="${item.link}"

            class="sidebar-link block px-4 py-2
            rounded-lg font-bold transition

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
    } else {

    /* =========================
       NORMAL MENU ITEM
    ========================== */
      const active = currentPath.endsWith(menu.link.toLowerCase());

      menuHTML += `

        <a
          href="${menu.link}"

          class="sidebar-link block py-[18px] px-[35px]
          text-white text-[18px] font-bold rounded-[18px]

          ${active ? "bg-[#3d5dff]" : "hover:bg-[#3d5dff]"}

          transition"
        >

          ${menu.name}

        </a>

      `;
    }
  });

  /* =========================
     RETURN SIDEBAR
  ========================== */

  return `

    <aside
      id="sidebar-container"

      class="hidden md:block sticky top-[110px]
      w-[300px] h-[calc(100vh-110px)]
      overflow-y-auto custom-scrollbar
      bg-[#020b24] p-5"
    >

      <nav class="flex flex-col gap-3">

        ${menuHTML}

      </nav>

    </aside>

  `;
}

/* ==================================================
   SUBMENU CLICK
================================================== */

document.addEventListener("click", function (e) {
  const btn = e.target.closest(".submenu-btn");

  if (!btn) {
    return;
  }

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

/* ==================================================
   SAVE SIDEBAR SCROLL
================================================== */

document.addEventListener("click", function (e) {
  const link = e.target.closest(".sidebar-link");

  if (!link) {
    return;
  }

  const sidebar = document.getElementById("sidebar-container");

  if (sidebar) {
    sessionStorage.setItem("sidebarScrollTop", sidebar.scrollTop);
  }
});

/* ==================================================
   RESTORE SIDEBAR SCROLL
================================================== */

window.addEventListener("DOMContentLoaded", function () {
  const savedScroll = sessionStorage.getItem("sidebarScrollTop");

  const sidebar = document.getElementById("sidebar-container");

  if (sidebar && savedScroll !== null) {
    sidebar.scrollTop = parseInt(savedScroll, 10);
  }
});
