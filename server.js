const express = require("express");
const session = require("express-session");
const path = require("path");
const fs = require("fs");
const multer = require("multer");
const Database = require("better-sqlite3");

const app = express();
const PORT = process.env.PORT || 3000;
const ROOT = __dirname;
const uploadDir = path.join(ROOT, "public", "uploads");
fs.mkdirSync(uploadDir, { recursive: true });

const db = new Database(path.join(ROOT, "data", "thaer.db"));
db.pragma("journal_mode = WAL");
db.exec(`
CREATE TABLE IF NOT EXISTS settings (key TEXT PRIMARY KEY, value TEXT NOT NULL);
CREATE TABLE IF NOT EXISTS pages (id INTEGER PRIMARY KEY AUTOINCREMENT, slug TEXT UNIQUE, title_ar TEXT, title_en TEXT, content_ar TEXT, content_en TEXT, visible INTEGER DEFAULT 1, sort_order INTEGER DEFAULT 0);
CREATE TABLE IF NOT EXISTS services (id INTEGER PRIMARY KEY AUTOINCREMENT, title_ar TEXT, title_en TEXT, description_ar TEXT, description_en TEXT, image TEXT, visible INTEGER DEFAULT 1, sort_order INTEGER DEFAULT 0);
CREATE TABLE IF NOT EXISTS portfolio (id INTEGER PRIMARY KEY AUTOINCREMENT, title TEXT, category TEXT, description TEXT, image TEXT, url TEXT, visible INTEGER DEFAULT 1, sort_order INTEGER DEFAULT 0);
CREATE TABLE IF NOT EXISTS social_links (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, url TEXT, icon TEXT, visible INTEGER DEFAULT 1, sort_order INTEGER DEFAULT 0);
`);

const defaults = {
  siteName: "Thaer Media",
  logo: "",
  favicon: "",
  primary: "#111111",
  accent: "#c7a86b",
  lightBg: "#f6f5f2",
  darkBg: "#0c0c0c",
  lightText: "#151515",
  darkText: "#f5f5f5",
  mode: "system",
  phone: "+970 599 351 383",
  whatsapp: "970599351383",
  email: "thaeralqrenawi@gmail.com",
  heroBadge_ar: "✦ وكالة إبداعية وتسويقية متكاملة",
  heroBadge_en: "✦ Integrated Creative & Marketing Agency",
  heroTitle_ar: "نوظِّفُ خبرتنا لنجاح علامتك التجارية",
  heroTitle_en: "We turn experience into brand success",
  heroText_ar: "أكثر من 12 عامًا من الخبرة في التصميم والتسويق وصناعة المحتوى، لنمنح علامتك التجارية حضورًا بصريًا واستراتيجيًا يصنع فرقًا حقيقيًا.",
  heroText_en: "More than 12 years of experience in design, marketing and content creation.",
  about_ar: "Thaer Media هي وكالة إبداعية وتسويقية تقدم حلولًا متكاملة للشركات، المتاجر، المطاعم، العيادات والأعمال بمختلف أنواعها حول العالم. نربط التصميم بالتسويق لنصنع أعمالًا جميلة وفعّالة في الوقت نفسه.",
  about_en: "Thaer Media is a creative and marketing agency providing integrated solutions for brands worldwide.",
  consultationPrice: "$15 / ساعة",
  consultationPrice_en: "$15 / hour"
};

for (const [k,v] of Object.entries(defaults)) {
  db.prepare("INSERT OR IGNORE INTO settings(key,value) VALUES(?,?)").run(k, v);
}
if (db.prepare("SELECT COUNT(*) c FROM services").get().c === 0) {
  const s = db.prepare("INSERT INTO services(title_ar,title_en,description_ar,description_en,sort_order) VALUES(?,?,?,?,?)");
  [
    ["الهوية البصرية","Brand Identity","نبني هوية بصرية متكاملة تعبّر عن شخصية علامتك.","We build complete visual identities that express your brand.",1],
    ["التصميم الإعلاني","Advertising Design","تصاميم إعلانية جذابة للحملات والمنصات الرقمية.","High-impact advertising visuals for digital campaigns.",2],
    ["صناعة المحتوى","Content Creation","محتوى بصري واستراتيجي يساعد علامتك على الظهور.","Visual and strategic content that helps your brand stand out.",3],
    ["التسويق الرقمي","Digital Marketing","حلول تسويقية مخصصة لزيادة الوصول وتحقيق النتائج.","Tailored marketing solutions focused on measurable results.",4]
  ].forEach(x => s.run(...x));
}
if (db.prepare("SELECT COUNT(*) c FROM portfolio").get().c === 0) {
  const p = db.prepare("INSERT INTO portfolio(title,category,description,image,url,sort_order) VALUES(?,?,?,?,?,?)");
  [
    ["Fast Plus · Kuwait","Branding","تصميم وحضور بصري","https://images.unsplash.com/photo-1558655146-d09347e92766?auto=format&fit=crop&w=1200&q=80","https://www.behance.net/",1],
    ["VIP Tourism · Palestine","Identity","هوية وتسويق","https://images.unsplash.com/photo-1556761175-b413da4baf72?auto=format&fit=crop&w=1200&q=80","https://www.behance.net/",2],
    ["Maram Trading · KSA","Advertising","تصميم إعلاني","https://images.unsplash.com/photo-1542744173-8e7e53415bb0?auto=format&fit=crop&w=1200&q=80","https://www.behance.net/",3]
  ].forEach(x => p.run(...x));
}
if (db.prepare("SELECT COUNT(*) c FROM social_links").get().c === 0) {
  const s = db.prepare("INSERT INTO social_links(name,url,icon,sort_order) VALUES(?,?,?,?)");
  [["Instagram","#","instagram",1],["Facebook","#","facebook",2],["Behance","https://www.behance.net/","behance",3],["WhatsApp","https://wa.me/970599351383","whatsapp",4]].forEach(x=>s.run(...x));
}

app.set("view engine","ejs");
app.set("views", path.join(ROOT,"views"));
app.use(express.urlencoded({extended:true}));
app.use(express.json());
app.use(express.static(path.join(ROOT,"public")));
app.use(session({
  secret: process.env.SESSION_SECRET || "change-this-secret",
  resave:false, saveUninitialized:false,
  cookie:{httpOnly:true, sameSite:"lax", secure:false}
}));

const upload = multer({ dest: uploadDir });

function settings() {
  return Object.fromEntries(db.prepare("SELECT key,value FROM settings").all().map(x=>[x.key,x.value]));
}
function admin(req,res,next){ if(!req.session.admin) return res.redirect("/admin/login"); next(); }

app.get("/", (req,res)=>{
  const lang = req.query.lang === "en" ? "en" : "ar";
  res.render("home", {
    lang, s: settings(),
    services: db.prepare("SELECT * FROM services WHERE visible=1 ORDER BY sort_order,id").all(),
    portfolio: db.prepare("SELECT * FROM portfolio WHERE visible=1 ORDER BY sort_order,id").all(),
    socials: db.prepare("SELECT * FROM social_links WHERE visible=1 ORDER BY sort_order,id").all()
  });
});

app.get("/page/:slug",(req,res)=>{
  const p=db.prepare("SELECT * FROM pages WHERE slug=? AND visible=1").get(req.params.slug);
  if(!p) return res.status(404).send("Page not found");
  const lang=req.query.lang==="en"?"en":"ar";
  res.render("page",{lang,s:settings(),page:p});
});

app.get("/admin/login",(req,res)=>res.render("login"));
app.post("/admin/login",(req,res)=>{
  const {username,password}=req.body;
  if(username === (process.env.ADMIN_USER||"admin") && password === (process.env.ADMIN_PASSWORD||"admin123")){
    req.session.admin=true; return res.redirect("/admin");
  }
  res.render("login",{error:"بيانات الدخول غير صحيحة"});
});
app.post("/admin/logout",(req,res)=>req.session.destroy(()=>res.redirect("/admin/login")));

app.get("/admin",admin,(req,res)=>{
  res.render("admin",{
    s:settings(),
    services:db.prepare("SELECT * FROM services ORDER BY sort_order,id").all(),
    portfolio:db.prepare("SELECT * FROM portfolio ORDER BY sort_order,id").all(),
    pages:db.prepare("SELECT * FROM pages ORDER BY sort_order,id").all(),
    socials:db.prepare("SELECT * FROM social_links ORDER BY sort_order,id").all()
  });
});

app.post("/admin/settings",admin,(req,res)=>{
  const allowed = Object.keys(defaults);
  const update=db.prepare("INSERT INTO settings(key,value) VALUES(?,?) ON CONFLICT(key) DO UPDATE SET value=excluded.value");
  const tx=db.transaction(()=>allowed.forEach(k=>{ if(req.body[k]!==undefined) update.run(k,String(req.body[k])); }));
  tx(); res.redirect("/admin#settings");
});

app.post("/admin/upload/logo",admin,upload.single("file"),(req,res)=>{
  if(!req.file) return res.redirect("/admin#settings");
  const ext=path.extname(req.file.originalname).toLowerCase() || ".png";
  const name="logo-"+Date.now()+ext;
  fs.renameSync(req.file.path,path.join(uploadDir,name));
  db.prepare("INSERT INTO settings(key,value) VALUES('logo',?) ON CONFLICT(key) DO UPDATE SET value=excluded.value").run("/uploads/"+name);
  res.redirect("/admin#settings");
});

app.post("/admin/services/save",admin,(req,res)=>{
  const b=req.body;
  if(b.id) db.prepare("UPDATE services SET title_ar=?,title_en=?,description_ar=?,description_en=?,visible=?,sort_order=? WHERE id=?").run(b.title_ar,b.title_en,b.description_ar,b.description_en,b.visible?1:0,b.sort_order||0,b.id);
  else db.prepare("INSERT INTO services(title_ar,title_en,description_ar,description_en,visible,sort_order) VALUES(?,?,?,?,?,?)").run(b.title_ar,b.title_en,b.description_ar,b.description_en,1,b.sort_order||0);
  res.redirect("/admin#services");
});
app.post("/admin/services/delete",admin,(req,res)=>{db.prepare("DELETE FROM services WHERE id=?").run(req.body.id);res.redirect("/admin#services")});

app.post("/admin/portfolio/save",admin,(req,res)=>{
  const b=req.body;
  if(b.id) db.prepare("UPDATE portfolio SET title=?,category=?,description=?,image=?,url=?,visible=?,sort_order=? WHERE id=?").run(b.title,b.category,b.description,b.image,b.url,b.visible?1:0,b.sort_order||0,b.id);
  else db.prepare("INSERT INTO portfolio(title,category,description,image,url,visible,sort_order) VALUES(?,?,?,?,?,?,?)").run(b.title,b.category,b.description,b.image,b.url,1,b.sort_order||0);
  res.redirect("/admin#portfolio");
});
app.post("/admin/portfolio/delete",admin,(req,res)=>{db.prepare("DELETE FROM portfolio WHERE id=?").run(req.body.id);res.redirect("/admin#portfolio")});

app.post("/admin/pages/save",admin,(req,res)=>{
  const b=req.body;
  if(b.id) db.prepare("UPDATE pages SET slug=?,title_ar=?,title_en=?,content_ar=?,content_en=?,visible=?,sort_order=? WHERE id=?").run(b.slug,b.title_ar,b.title_en,b.content_ar,b.content_en,b.visible?1:0,b.sort_order||0,b.id);
  else db.prepare("INSERT INTO pages(slug,title_ar,title_en,content_ar,content_en,visible,sort_order) VALUES(?,?,?,?,?,?,?)").run(b.slug,b.title_ar,b.title_en,b.content_ar,b.content_en,1,b.sort_order||0);
  res.redirect("/admin#pages");
});
app.post("/admin/pages/delete",admin,(req,res)=>{db.prepare("DELETE FROM pages WHERE id=?").run(req.body.id);res.redirect("/admin#pages")});

app.post("/admin/social/save",admin,(req,res)=>{
  const b=req.body;
  if(b.id) db.prepare("UPDATE social_links SET name=?,url=?,icon=?,visible=?,sort_order=? WHERE id=?").run(b.name,b.url,b.icon,b.visible?1:0,b.sort_order||0,b.id);
  else db.prepare("INSERT INTO social_links(name,url,icon,visible,sort_order) VALUES(?,?,?,?,?)").run(b.name,b.url,b.icon,1,b.sort_order||0);
  res.redirect("/admin#social");
});
app.post("/admin/social/delete",admin,(req,res)=>{db.prepare("DELETE FROM social_links WHERE id=?").run(req.body.id);res.redirect("/admin#social")});

app.listen(PORT,()=>console.log(`Thaer Media CMS running on http://localhost:${PORT}`));