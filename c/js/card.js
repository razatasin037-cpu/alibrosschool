console.log("card.js loaded");

function createCodeCard({
  containerId,
  title,
  code,
}) {

  const container = document.getElementById(containerId);

  if (!container) {
    console.log("Container not found");
    return;
  }

  container.innerHTML += `
    <div class="bg-gradient-to-r from-[#121826] to-[#1b2440] rounded-[30px] border border-indigo-500 overflow-hidden shadow-xl">

      <div class="p-8 border-b border-white/10">
        <h2 class="text-3xl md:text-4xl font-bold text-white">
          ${title}
        </h2>
      </div>

      <div class="p-6">
        <div class="bg-[#0b1120] border-l-[6px] border-blue-500 rounded-xl p-8 overflow-x-auto">

          <pre class="text-lg md:text-xl leading-8 text-white whitespace-pre-wrap"><code>${code}</code></pre>

        </div>
      </div>

    </div>
  `;
}


createCodeCard({
  containerId: "codeCards",
  title: "Hello World Example",
  code: `#include <stdio.h>

int main()
{
    printf("Hello World");
    return 0;
}`
});