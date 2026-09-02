
function createCodeCard({
  containerId,
  title,
  paragraph="",
  code,
}) {

  const container = document.getElementById(containerId);

  if (!container) {
    console.log("Container not found");
    return;
  }

  container.innerHTML += `
    <div class="bg-gradient-to-r from-[#121826] to-[#1b2440] rounded-[30px] border border-indigo-500 overflow-hidden shadow-xl">

      <div class="py-5 px-5 border-b border-white/10">
        <h2 class="text-2xl md:text-4xl font-bold text-white">
          ${title}
        </h2>

        <p class="text-xl text-gray-300 leading-9 ">
           ${paragraph}
     </p>
      </div>

      <div class="p-6">
        <div class="bg-[#0b1120] border-l-[6px] border-blue-500 rounded-xl p-4 md:p-8 overflow-x-auto">

          <pre class="text-md md:text-xl leading-8 text-white whitespace-pre-wrap"><code>${code}</code></pre>

        </div>
      </div>

    </div>
  `;
}


createCodeCard({
  containerId: "codeCards",
  title: "Hello World Example",
  code: `#include &lt;stdio.h&gt;

<span class="text-green-400">int</span> main()
{
    printf(<span class="text-green-400">"Hello World"</span>);
    <span class="text-green-400">return 0</span>;
}`
});

// syntax code challenge 1 ka h
createCodeCard({
  containerId: "ChallengeSyntax1",
  title: "Challenge 1: Complete the Program",
  paragraph:"Neeche diye gaye code me missing keyword fill karke program complete karo.",
  code: `#include &lt;stdio.h&gt;

<span class="text-yellow-400">_______</span> main()
{
    printf(<span class="text-green-400">"Hello World"</span>);
    <span class="text-green-400">return 0</span>;
}`
});

// syntax code challenge 2 ka h
createCodeCard({
  containerId: "Challenge2",
  title: "Challenge 2: Find the Error",
  code: `<span class="text-red-400">#include &lt;stdio.h&gt;

<span >int</span> main()
{
    printf(<span>"Welcome"</span>)
    <span>return 0</span>;</span>
}`
});

//syntax code challenge 3 ka h
createCodeCard({
  containerId: "challenge3",
  title: "Challenge 3: Predict the Output",
  code: `#include &lt;stdio.h&gt;

<span class="text-green-400">int</span> main()
{
    printf(<span class="text-green-400">"Learning C"</span>);
    <span class="text-green-400">return 0</span>;
}`
});

//output code multiple Text ka h
createCodeCard({
  containerId: "multiple",
  title: "Print Multiple Text",
  code: `#include &lt;stdio.h&gt;

<span class="text-green-400">int</span> main()
{
    printf(<span class="text-green-400">"Learning C"</span>);
    printf(<span class="text-green-400">"Welcome"</span>);
    printf(<span class="text-green-400">" to"</span>);
    printf(<span class="text-green-400">" C Programming"</span>);
    <span class="text-green-400">return 0</span>;
}`
});

// output code new lines
createCodeCard({
  containerId: "newLineOutput",
  title: "Print Multiple Text",
  code: `#include &lt;stdio.h&gt;

<span class="text-green-400">int</span> main()
{
    printf(<span class="text-green-400">"Hello\\n"</span>);
    printf(<span class="text-green-400">"Welcome"</span>);
    <span class="text-green-400">return 0</span>;
}`
});

// output code new lines
createCodeCard({
  containerId: "multipleNewLineOutput",
  title: "Multiple New Lines Example",
  code: `#include &lt;stdio.h&gt;

<span class="text-green-400">int</span> main()
{
    printf(<span class="text-green-400">"HTML\\n"</span>);
    printf(<span class="text-green-400">"CSS\\n"</span>);
    printf(<span class="text-green-400">"JavaScript\\n"</span>);
    printf(<span class="text-green-400">"C Programming\\n"</span>);
    <span class="text-green-400">return 0</span>;
}`
});

// Comments code single line comments
createCodeCard({
  containerId: "singleLineComments",
  title: "Single Line Comment Example",
  code: `#include &lt;stdio.h&gt;

<span class="text-green-400">int</span> main()
{
    <span class="text-white/50">// Print Hello World</span>
    printf(<span class="text-green-400">"Hello World!"</span>);
    <span class="text-green-400">return 0</span>;
}`
});


