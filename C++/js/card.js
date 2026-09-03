console.log("card.js loaded");

function createCodeCard(title, code) {
  return `
    <div
      class="
        bg-white/[0.04]
        border border-white/[0.08]
        rounded-3xl
        overflow-hidden
        backdrop-blur-xl
        shadow-[0_0_30px_rgba(77,111,255,0.10)]
        mb-10
      "
    >

      <!-- Card Header -->
      <div
        class="
          px-6 sm:px-8
          py-5
          bg-[#111827]
          border-b border-white/[0.08]
        "
      >

        <div class="flex items-center gap-3">

          <!-- Icon -->
          <div
            class="
              w-11 h-11
              rounded-xl
              bg-[#4d6fff]/15
              border border-[#4d6fff]/30
              flex items-center justify-center
              shrink-0
            "
          >
            <i class="fa-solid fa-code text-[#4d6fff] text-lg"></i>
          </div>

          <!-- Title -->
          <h2
            class="
              text-xl
              sm:text-2xl
              font-bold
              text-white
            "
          >
            ${title}
          </h2>

        </div>

      </div>


      <!-- Code Area -->
      <div class="p-5 sm:p-7">

        <div
          class="
            bg-[#020617]
            border border-white/[0.08]
            border-l-4
            border-l-[#4d6fff]
            rounded-2xl
            p-5 sm:p-7
            overflow-x-auto
          "
        >

          <pre
            class="
              text-sm
              sm:text-base
              lg:text-lg
              leading-8
              font-mono
              text-gray-200
              whitespace-pre-wrap
            "
          >${code}</pre>

        </div>

      </div>

    </div>
  `;
}

//myfirstprogram.cpp card//
function createCppQuickstartCard() {
  return `
    <div class="w-full max-w-7xl mx-auto mt-10 mb-12">

      <div class="
        bg-white/[0.04]
        border border-white/[0.08]
        rounded-3xl
        overflow-hidden
        backdrop-blur-xl
        shadow-[0_0_30px_rgba(77,111,255,0.10)]
      ">

        <div class="
          px-6 sm:px-8 py-5
          bg-[#111827]
          border-b border-white/[0.08]
        ">
          <div class="flex items-center gap-3">

            <div class="
              w-10 h-10 rounded-xl
              bg-[#4d6fff]/15
              border border-[#4d6fff]/30
              flex items-center justify-center
            ">
              <i class="fa-solid fa-code text-[#4d6fff]"></i>
            </div>

            <h2 class="text-xl sm:text-2xl font-bold text-white">
              myfirstprogram.cpp
            </h2>

          </div>
        </div>

        <div class="p-5 sm:p-8">

          <div class="
            bg-[#020617]
            border border-white/[0.08]
            border-l-4 border-l-[#4d6fff]
            rounded-2xl
            p-5 sm:p-7
            overflow-x-auto
          ">

<pre class="
  text-sm sm:text-base lg:text-lg
  leading-8 font-mono text-gray-200
"><code><span class="text-[#4d6fff]">#include</span> &lt;iostream&gt;

<span class="text-[#4d6fff]">using namespace</span> std;

<span class="text-[#4d6fff]">int</span> main() {

    cout &lt;&lt; <span class="text-green-400">"Hello World!"</span>;

    <span class="text-[#4d6fff]">return</span> 0;
}</code></pre>

          </div>

        </div>

      </div>

      <div class="
        mt-8
        bg-white/[0.04]
        border border-white/[0.08]
        rounded-3xl
        p-6 sm:p-8
      ">

        <p class="
          text-gray-300
          text-lg sm:text-xl
          leading-relaxed
        ">
          Don't worry if you don't understand the code above -
          we will discuss it in detail in later chapters.
          For now, focus on how to run the code.
        </p>

      </div>

    </div>
  `;
}
