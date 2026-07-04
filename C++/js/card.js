console.log("card.js loaded");

function createCodeCard(title, code) {
  return `
    <div class="bg-gradient-to-r from-[#121826] to-[#1b2440] rounded-[30px] border border-indigo-500 overflow-hidden">
      <div class="p-8">
        <h2 class="text-4xl font-bold">${title}</h2>
      </div>

      <div class="px-4 pb-6">
        <div class="bg-[#0b1120] border-l-[6px] border-blue-500 rounded-xl p-8 overflow-auto">
          <pre class="text-[22px] leading-8 text-white whitespace-pre-wrap">${code}</pre>
        </div>
      </div>
    </div>
  `;
}
