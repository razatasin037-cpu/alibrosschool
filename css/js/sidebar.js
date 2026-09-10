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
    `;

    document.head.appendChild(style);
  }

  const menus = [
    { name: "CSS HOME", link: "/CSS/home/css-tutorial.html" },
    { name: "CSS Introduction", link: "/CSS/intro/intro.html" },
    { name: "CSS Syntax", link: "/CSS/syntax/syntax.html" },
    { name: "CSS Selectors", link: "/CSS/selectors/selectors.html" },
    {
      name: "CSS Grouping Selectors",
      link: "/CSS/grouping-selectors/grouping-selectors.html",
    },
    { name: "CSS How To", link: "/CSS/how-to/how-to.html" },
    { name: "CSS Comments", link: "/CSS/comments/comments.html" },
    { name: "CSS Errors", link: "/CSS/errors/errors.html" },
    { name: "CSS Colors", link: "/CSS/colors/colors.html" },
    { name: "CSS Backgrounds", link: "/CSS/backgrounds/backgrounds.html" },
    { name: "CSS Borders", link: "/CSS/borders/borders.html" },
    { name: "CSS Margins", link: "/CSS/margins/margins.html" },
    { name: "CSS Padding", link: "/CSS/padding/padding.html" },
    { name: "CSS Height / Width", link: "/CSS/height-width/height-width.html" },
    { name: "CSS Box Model", link: "/CSS/box-model/box-model.html" },
    { name: "CSS Outline", link: "/CSS/outline/outline.html" },
    { name: "CSS Text", link: "/CSS/text/text.html" },
    { name: "CSS Fonts", link: "/CSS/fonts/fonts.html" },
    { name: "CSS Icons", link: "/CSS/icons/icons.html" },
    { name: "CSS Links", link: "/CSS/links/links.html" },
    { name: "CSS Lists", link: "/CSS/lists/lists.html" },
    { name: "CSS Tables", link: "/CSS/tables/tables.html" },
    { name: "CSS Display", link: "/CSS/display/display.html" },
    { name: "CSS Position", link: "/CSS/position/position.html" },
    { name: "CSS Z-Index", link: "/CSS/z-index/z-index.html" },
    { name: "CSS Overflow", link: "/CSS/overflow/overflow.html" },
    { name: "CSS Float", link: "/CSS/float/float.html" },
    { name: "CSS Inline-Block", link: "/CSS/inline-block/inline-block.html" },
    { name: "CSS Align", link: "/CSS/align/align.html" },
    { name: "CSS Combinators", link: "/CSS/combinators/combinators.html" },
    {
      name: "CSS Pseudo-Classes",
      link: "/CSS/pseudo-classes/pseudo-classes.html",
    },
    {
      name: "CSS Pseudo-Elements",
      link: "/CSS/pseudo-elements/pseudo-elements.html",
    },
    { name: "CSS Opacity", link: "/CSS/opacity/opacity.html" },

    { name: "CSS Navigation Bar", link: "/CSS/navbar/navbar.html" },
    { name: "CSS Dropdowns", link: "/CSS/dropdowns/dropdowns.html" },
    {
      name: "CSS Image Gallery",
      link: "/CSS/image-gallery/image-gallery.html",
    },
    {
      name: "CSS Image Sprites",
      link: "/CSS/image-sprites/image-sprites.html",
    },
    {
      name: "CSS Attribute Selectors",
      link: "/CSS/attribute-selectors/attribute-selectors.html",
    },
    { name: "CSS Forms", link: "/CSS/forms/forms.html" },
    { name: "CSS Counters", link: "/CSS/counters/counters.html" },
    { name: "CSS Units", link: "/CSS/units/units.html" },
    { name: "CSS Inheritance", link: "/CSS/inheritance/inheritance.html" },
    { name: "CSS Specificity", link: "/CSS/specificity/specificity.html" },
    { name: "CSS !important", link: "/CSS/important/important.html" },
    { name: "CSS Optimization", link: "/CSS/optimization/optimization.html" },
    {
      name: "CSS Accessibility",
      link: "/CSS/accessibility/accessibility.html",
    },
    { name: "CSS Website Layout", link: "/CSS/layout/layout.html" },

    {
      name: "CSS Rounded Corners",
      link: "/CSS/rounded-corners/rounded-corners.html",
    },
    {
      name: "CSS Border Images",
      link: "/CSS/border-images/border-images.html",
    },
    {
      name: "CSS Multiple Backgrounds",
      link: "/CSS/multiple-backgrounds/multiple-backgrounds.html",
    },
    {
      name: "CSS Background Size",
      link: "/CSS/background-size/background-size.html",
    },
    { name: "CSS Gradients", link: "/CSS/gradients/gradients.html" },
    { name: "CSS Shadows", link: "/CSS/shadows/shadows.html" },
    { name: "CSS Text Effects", link: "/CSS/text-effects/text-effects.html" },
    { name: "CSS Custom Fonts", link: "/CSS/custom-fonts/custom-fonts.html" },
    {
      name: "CSS 2D Transforms",
      link: "/CSS/2d-transforms/2d-transforms.html",
    },
    {
      name: "CSS 3D Transforms",
      link: "/CSS/3d-transforms/3d-transforms.html",
    },
    { name: "CSS Transitions", link: "/CSS/transitions/transitions.html" },
    { name: "CSS Animations", link: "/CSS/animations/animations.html" },
    { name: "CSS Tooltips", link: "/CSS/tooltips/tooltips.html" },
    {
      name: "CSS Image Styling",
      link: "/CSS/image-styling/image-styling.html",
    },
    {
      name: "CSS Image Effects",
      link: "/CSS/image-effects/image-effects.html",
    },
    {
      name: "CSS Image Filters",
      link: "/CSS/image-filters/image-filters.html",
    },
    { name: "CSS Object Fit", link: "/CSS/object-fit/object-fit.html" },
    {
      name: "CSS Object Position",
      link: "/CSS/object-position/object-position.html",
    },
    { name: "CSS Masking", link: "/CSS/masking/masking.html" },
    { name: "CSS Buttons", link: "/CSS/buttons/buttons.html" },
    { name: "CSS Pagination", link: "/CSS/pagination/pagination.html" },
    {
      name: "CSS Multiple Columns",
      link: "/CSS/multiple-columns/multiple-columns.html",
    },
    {
      name: "CSS User Interface",
      link: "/CSS/user-interface/user-interface.html",
    },

    { name: "CSS Variables", link: "/CSS/variables/variables.html" },
    { name: "CSS @property", link: "/CSS/property/property.html" },
    { name: "CSS Box Sizing", link: "/CSS/box-sizing/box-sizing.html" },
    {
      name: "CSS Media Queries",
      link: "/CSS/media-queries/media-queries.html",
    },

    { name: "CSS Flexbox", link: "/CSS/flexbox/flexbox.html" },
    {
      name: "CSS Flex Container",
      link: "/CSS/flex-container/flex-container.html",
    },
    {
      name: "CSS Justify Content",
      link: "/CSS/justify-content/justify-content.html",
    },
    { name: "CSS Align Items", link: "/CSS/align-items/align-items.html" },
    { name: "CSS Flex Items", link: "/CSS/flex-items/flex-items.html" },
    {
      name: "CSS Flex Responsive",
      link: "/CSS/flex-responsive/flex-responsive.html",
    },

    { name: "CSS Grid", link: "/CSS/grid/grid.html" },
    {
      name: "CSS Grid Container",
      link: "/CSS/grid-container/grid-container.html",
    },
    { name: "CSS Grid Tracks", link: "/CSS/grid-tracks/grid-tracks.html" },
    { name: "CSS Grid Gaps", link: "/CSS/grid-gaps/grid-gaps.html" },
    { name: "CSS Grid Align", link: "/CSS/grid-align/grid-align.html" },
    { name: "CSS Grid Items", link: "/CSS/grid-items/grid-items.html" },
    {
      name: "CSS Named Grid Items",
      link: "/CSS/named-grid-items/named-grid-items.html",
    },
    {
      name: "CSS Grid Item Alignment",
      link: "/CSS/grid-item-align/grid-item-align.html",
    },
    {
      name: "CSS Grid Item Order",
      link: "/CSS/grid-item-order/grid-item-order.html",
    },
    {
      name: "CSS 12-Column Grid",
      link: "/CSS/12-column-grid/12-column-grid.html",
    },

    {
      name: "CSS Responsive Web Design",
      link: "/CSS/responsive/responsive.html",
    },
    { name: "CSS Viewport", link: "/CSS/responsive/viewport.html" },
 
  ];

  const pathParts = window.location.pathname.split("/");
  const currentFileName = pathParts[pathParts.length - 1].toLowerCase();

  let menuHTML = "";

  menus.forEach((menu, index) => {
    const menuFileName = menu.link.split("/").pop().toLowerCase();

    const active = currentFileName === menuFileName || index === activeIndex;

    menuHTML += `
      <a
        href="${menu.link}"
        class="sidebar-link block py-[14px] px-[25px]
        text-white text-[16px] font-bold rounded-[14px]
        ${active ? "bg-[#3d5dff]" : "hover:bg-[#3d5dff]"}
        transition duration-200"
      >
        ${menu.name}
      </a>
    `;
  });

  return `
    <aside
      id="sidebar-container"
      class="hidden md:block sticky top-[110px]
      w-[310px] h-[calc(100vh-110px)]
      overflow-y-auto custom-scrollbar
      bg-[#020b24] p-5"
    >

      <h5
        class="text-[#7f8db4] tracking-[5px]
        text-[20px] font-bold mb-5 ml-5 uppercase"
      >
        CSS Tutorial
      </h5>

      <nav class="flex flex-col gap-2">
        ${menuHTML}
      </nav>

    </aside>
  `;
}

document.addEventListener("click", function (e) {
  const link = e.target.closest(".sidebar-link");

  if (link) {
    const sidebar = document.getElementById("sidebar-container");

    if (sidebar) {
      sessionStorage.setItem("sidebarScrollTop", sidebar.scrollTop);
    }
  }
});

window.addEventListener("DOMContentLoaded", function () {
  const savedScroll = sessionStorage.getItem("sidebarScrollTop");

  const sidebar = document.getElementById("sidebar-container");

  if (sidebar && savedScroll !== null) {
    sidebar.scrollTop = parseInt(savedScroll, 10);
  }
});
