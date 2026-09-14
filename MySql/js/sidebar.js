function createSidebar(activeIndex = 0) {
  if (!document.getElementById("sidebar-style")) {
    const style = document.createElement("style");

    style.id = "sidebar-style";

    style.innerHTML = `
      .custom-scrollbar::-webkit-scrollbar {
        width: 4px;
      }

      .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
      }

      .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #3158ff;
        border-radius: 10px;
      }

      .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #4d6fff;
      }

      .custom-scrollbar {
        scrollbar-width: thin;
        scrollbar-color: #3158ff transparent;
      }

      #sidebar-container {
        border: none !important;
      }

      .sidebar-link {
        text-decoration: none;
      }
    `;

    document.head.appendChild(style);
  }

  const menus = [
    {
      name: "MySQL HOME",
      link: "/MySQL/home/mysql-tutorial.html",
    },

    {
      name: "MySQL Introduction",
      link: "/MySQL/introduction/mysql-introduction.html",
    },

    {
      name: "Installing MySQL",
      link: "/MySQL/installing-mysql/installingmysql.html",
    },

    {
      name: "MySQL Workbench",
      link: "/MySQL/workbench/workbench.html",
    },

    {
      name: "Creating a Database",
      link: "/MySQL/creating-database/creatingdatabase.html",
    },

    {
      name: "Creating Tables",
      link: "/MySQL/creating-tables/creatingtables.html",
    },

    {
      name: "Data Types",
      link: "/MySQL/data-types/datatypes.html",
    },

    {
      name: "INSERT Data",
      link: "/MySQL/insert-data/insertdata.html",
    },

    {
      name: "SELECT Data",
      link: "/MySQL/select-data/selectdata.html",
    },

    {
      name: "WHERE Clause",
      link: "/MySQL/where-clause/whereclause.html",
    },

    {
      name: "ORDER BY",
      link: "/MySQL/order-by/orderby.html",
    },

    {
      name: "UPDATE Data",
      link: "/MySQL/update-data/updatedata.html",
    },

    {
      name: "DELETE Data",
      link: "/MySQL/delete-data/deletedata.html",
    },

    {
      name: "LIMIT",
      link: "/MySQL/limit/limit.html",
    },

    {
      name: "DISTINCT",
      link: "/MySQL/distinct/distinct.html",
    },

    {
      name: "LIKE",
      link: "/MySQL/like/like.html",
    },

    {
      name: "IN",
      link: "/MySQL/in/in.html",
    },

    {
      name: "BETWEEN",
      link: "/MySQL/between/between.html",
    },

    {
      name: "Aliases",
      link: "/MySQL/aliases/aliases.html",
    },

    {
      name: "Aggregate Functions",
      link: "/MySQL/aggregate-functions/aggregatefunctions.html",
    },

    {
      name: "GROUP BY",
      link: "/MySQL/group-by/groupby.html",
    },

    {
      name: "HAVING",
      link: "/MySQL/having/having.html",
    },

    {
      name: "JOIN",
      link: "/MySQL/join/join.html",
    },

    {
      name: "INNER JOIN",
      link: "/MySQL/inner-join/innerjoin.html",
    },

    {
      name: "LEFT JOIN",
      link: "/MySQL/left-join/leftjoin.html",
    },

    {
      name: "RIGHT JOIN",
      link: "/MySQL/right-join/rightjoin.html",
    },

    {
      name: "FULL JOIN",
      link: "/MySQL/full-join/fulljoin.html",
    },

    {
      name: "UNION",
      link: "/MySQL/union/union.html",
    },

    {
      name: "CASE",
      link: "/MySQL/case/case.html",
    },

    {
      name: "Subqueries",
      link: "/MySQL/subqueries/subqueries.html",
    },

    {
      name: "EXISTS",
      link: "/MySQL/exists/exists.html",
    },

    {
      name: "CREATE DATABASE",
      link: "/MySQL/create-database/createdatabase.html",
    },

    {
      name: "CREATE TABLE",
      link: "/MySQL/create-table/createtable.html",
    },

    {
      name: "ALTER TABLE",
      link: "/MySQL/alter-table/altertable.html",
    },

    {
      name: "DROP TABLE",
      link: "/MySQL/drop-table/droptable.html",
    },

    {
      name: "Constraints",
      link: "/MySQL/constraints/constraints.html",
    },

    {
      name: "PRIMARY KEY",
      link: "/MySQL/primary-key/primarykey.html",
    },

    {
      name: "FOREIGN KEY",
      link: "/MySQL/foreign-key/foreignkey.html",
    },

    {
      name: "INDEX",
      link: "/MySQL/index/index.html",
    },

    {
      name: "Views",
      link: "/MySQL/views/views.html",
    },

    {
      name: "Stored Procedures",
      link: "/MySQL/stored-procedures/storedprocedures.html",
    },

    {
      name: "SQL Injection",
      link: "/MySQL/sql-injection/sqlinjection.html",
    },
  ];

  const currentPath = window.location.pathname.toLowerCase();

  const menuHTML = menus
    .map((menu, index) => {
      const menuPath = new URL(
        menu.link,
        window.location.origin,
      ).pathname.toLowerCase();

      const isActive =
        index === activeIndex ||
        currentPath === menuPath ||
        currentPath.endsWith(menuPath);

      return `
        <a
          href="${menu.link}"
          class="sidebar-link block px-4 py-3 rounded-xl
          text-lg font-semibold
          ${
            isActive
              ? "bg-[#3158ff] text-white"
              : "text-gray-200 hover:bg-[#16213d]"
          }
          transition"
        >
          ${menu.name}
        </a>
      `;
    })
    .join("");

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

document.addEventListener("DOMContentLoaded", function () {
  const sidebar = document.getElementById("sidebar");

  if (sidebar && typeof createSidebar === "function") {
    sidebar.innerHTML = createSidebar(0);
  }
});

document.addEventListener("click", function (e) {
  const link = e.target.closest(".sidebar-link");

  if (!link) {
    return;
  }

  const sidebar = document.getElementById("sidebar-container");

  if (sidebar) {
    sessionStorage.setItem("mysqlSidebarScrollTop", sidebar.scrollTop);
  }
});

window.addEventListener("DOMContentLoaded", function () {
  const savedScroll = sessionStorage.getItem("mysqlSidebarScrollTop");

  const sidebar = document.getElementById("sidebar-container");

  if (sidebar && savedScroll !== null) {
    sidebar.scrollTop = parseInt(savedScroll, 10);
  }
});
