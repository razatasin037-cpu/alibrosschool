document.querySelectorAll('[data-confirm]').forEach(el=>{
  el.addEventListener('click',e=>{
    if(!confirm(el.dataset.confirm)) e.preventDefault();
  });
});
document.querySelectorAll('[data-autofocus]').forEach(el=>el.focus());
