const langBtn=document.getElementById('langBtn');
const menuBtn=document.getElementById('menuBtn');
const mobileNav=document.getElementById('mobileNav');
let lang=localStorage.getItem('thaer-lang')||'ar';

function applyLang(){
  document.documentElement.lang=lang;
  document.documentElement.dir=lang==='ar'?'rtl':'ltr';
  document.body.classList.toggle('en-mode',lang==='en');
  document.querySelectorAll('[data-ar][data-en]').forEach(el=>{el.innerHTML=el.dataset[lang]});
  document.querySelectorAll('img[data-ar-src][data-en-src]').forEach(img=>{
    img.src=lang==='ar'?img.dataset.arSrc:img.dataset.enSrc;
    img.alt=lang==='ar'?(img.getAttribute('data-ar-alt')||img.alt):(img.getAttribute('data-en-alt')||img.alt);
  });
  document.querySelectorAll('[data-ar-href][data-en-href]').forEach(el=>{
    el.href=lang==='ar'?el.dataset.arHref:el.dataset.enHref;
  });
  document.querySelectorAll('input,textarea').forEach(el=>{
    if(el.id==='name')el.placeholder=lang==='ar'?'الاسم الكامل':'Full name';
    if(el.id==='email')el.placeholder='you@example.com';
    if(el.id==='message')el.placeholder=lang==='ar'?'أخبرنا باختصار عن مشروعك...':'Tell us briefly about your project...';
  });
  langBtn.textContent=lang==='ar'?'EN':'AR';
  document.querySelectorAll('.mobile-nav a').forEach(a=>a.setAttribute('aria-current','false'));
}

langBtn?.addEventListener('click',()=>{lang=lang==='ar'?'en':'ar';localStorage.setItem('thaer-lang',lang);applyLang()});
menuBtn?.addEventListener('click',()=>mobileNav.classList.toggle('open'));
document.querySelectorAll('.mobile-nav a').forEach(a=>a.addEventListener('click',()=>mobileNav.classList.remove('open')));

document.querySelectorAll('.service-card[data-work-target]').forEach(card=>{
  const go=()=>{
    const target=document.getElementById(card.dataset.workTarget);
    if(target){target.scrollIntoView({behavior:'smooth',block:'center'});}
  };
  card.addEventListener('click',go);
  card.addEventListener('keydown',e=>{if(e.key==='Enter'||e.key===' '){e.preventDefault();go();}});
  card.setAttribute('role','link');
  card.setAttribute('tabindex','0');
});

document.getElementById('contactForm')?.addEventListener('submit',e=>{
  e.preventDefault();
  const name=document.getElementById('name').value.trim();
  const email=document.getElementById('email').value.trim();
  const message=document.getElementById('message').value.trim();
  const text=lang==='ar'
    ?`مرحباً Thaer Media،%0A%0Aالاسم: ${encodeURIComponent(name)}%0Aالبريد: ${encodeURIComponent(email)}%0A%0Aتفاصيل المشروع:%0A${encodeURIComponent(message)}`
    :`Hello Thaer Media,%0A%0AName: ${encodeURIComponent(name)}%0AEmail: ${encodeURIComponent(email)}%0A%0AProject details:%0A${encodeURIComponent(message)}`;
  window.open(`https://wa.me/970599351383?text=${text}`,'_blank');
});

applyLang();
