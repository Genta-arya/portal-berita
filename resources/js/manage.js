document.querySelectorAll('[data-edit-row]').forEach(button=>button.addEventListener('click',()=>{
 const data=JSON.parse(button.dataset.editRow),form=document.getElementById('management-form');
 for(const [key,value] of Object.entries(data)){
  const field=form.elements.namedItem(key);if(!field||field.type==='password')continue;
  let next=value??'';if(['starts_at','ends_at'].includes(key)&&next){const utc=new Date(String(next).replace(' ','T')+'Z');next=new Intl.DateTimeFormat('sv-SE',{timeZone:'Asia/Jakarta',year:'numeric',month:'2-digit',day:'2-digit',hour:'2-digit',minute:'2-digit'}).format(utc).replace(' ','T');}
  field.value=String(next);
 }form.scrollIntoView({behavior:'smooth',block:'start'});form.querySelector('input')?.focus();
}));
