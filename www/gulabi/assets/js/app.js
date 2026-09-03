window.addEventListener('load',()=>{
    setTimeout(()=>document.getElementById('loader')?.classList.add('hide'),450);
});

function toggleSidebar(){
    document.getElementById('sidebar')?.classList.toggle('open');
}

function selectAll(className, checked){
    document.querySelectorAll('.'+className).forEach(x=>x.checked=checked);
    updateSelected();
}

function updateSelected(){
    const boxes=[...document.querySelectorAll('.pay-box:checked')];
    const total=boxes.reduce((s,x)=>s+Number(x.dataset.amount||0),0);
    const count=document.getElementById('selectedCount');
    const amount=document.getElementById('selectedAmount');
    if(count) count.textContent=boxes.length;
    if(amount) amount.textContent='₹'+total.toLocaleString('en-IN',{minimumFractionDigits:2,maximumFractionDigits:2});
}
