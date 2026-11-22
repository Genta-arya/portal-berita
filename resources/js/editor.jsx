import React, {useState,useRef,useEffect} from 'react';
import {createRoot} from 'react-dom/client';
import {Jodit} from 'jodit';
import 'jodit/esm/plugins/fullsize/fullsize.js';
import 'jodit/esm/plugins/preview/preview.js';
import 'jodit/esm/plugins/search/search.js';
import 'jodit/esm/plugins/source/source.js';
import 'jodit/esm/plugins/clean-html/clean-html.js';
import 'jodit/esm/plugins/paste/paste.js';
import 'jodit/esm/plugins/paste-from-word/paste-from-word.js';
import 'jodit/esm/plugins/video/config.js';
import 'jodit/es2021/jodit.min.css';

function Editor({initial,categories}) {
 const [data,setData]=useState(initial),[message,setMessage]=useState('Belum ada perubahan'),[dirty,setDirty]=useState(false),[media,setMedia]=useState([]),[busy,setBusy]=useState(false),[editor,setEditor]=useState(null),[words,setWords]=useState(0),[mediaMode,setMediaMode]=useState('body');
 const mount=useRef(null),current=useRef(initial),changed=useRef(false),saving=useRef(false),selection=useRef(0);
 const update=(key,value)=>{const next={...current.current,[key]:value};current.current=next;setData(next);changed.current=true;setDirty(true);};
 const loadMedia=async(mode='body')=>{setMediaMode(mode);try{const r=await fetch('/admin/media',{headers:{Accept:'application/json'}});if(!r.ok)throw new Error();setMedia((await r.json()).data);}catch{setMessage('Media tidak dapat dimuat.');}};
 useEffect(()=>{
  const host=document.createElement('textarea');host.value=initial.body??'';mount.current.appendChild(host);
  const buttons=['undo','redo','|','paragraph','bold','italic','underline','strikethrough','|','ul','ol','|','link',{name:'newsMedia',icon:'image',tooltip:'Pilih gambar dari media',exec:j=>{j.s.save();loadMedia();}},{name:'newsVideo',icon:'video',tooltip:'Video YouTube',exec:j=>{
    j.s.save();const url=window.prompt('URL video YouTube');if(!url)return;
    try{const u=new URL(url);const id=u.hostname==='youtu.be'?u.pathname.slice(1):['youtube.com','www.youtube.com','www.youtube-nocookie.com'].includes(u.hostname)?(u.searchParams.get('v')??u.pathname.match(/^\/embed\/([A-Za-z0-9_-]{11})$/)?.[1]):null;if(!/^[A-Za-z0-9_-]{11}$/.test(id??''))throw new Error();j.s.restore();j.s.insertHTML(`<iframe src="https://www.youtube-nocookie.com/embed/${id}" width="560" height="315" title="Video YouTube"></iframe><p><br></p>`);}catch{setMessage('Masukkan URL YouTube yang valid.');}
  }},'table','|','eraser','find','fullsize','preview','source'];
  const j=Jodit.make(host,{readonly:!initial.editable,height:500,minHeight:400,language:'en',sourceEditor:'area',spellcheck:true,toolbarAdaptive:false,toolbarSticky:true,buttons,controls:{paragraph:{list:{p:'Paragraph',h2:'Heading 2',h3:'Heading 3',blockquote:'Quote'}}},showCharsCounter:false,showWordsCounter:false,showXPathInStatusbar:false,askBeforePasteHTML:true,askBeforePasteFromWord:true,disablePlugins:['file','filebrowser','image','video','image-properties','drag-and-drop','drag-and-drop-element','paste-storage']});
  j.editor.setAttribute('aria-label','Isi berita');j.editor.setAttribute('role','textbox');j.editor.setAttribute('aria-multiline','true');
  let previous=j.value;
  const count=()=>setWords((j.editor.textContent??'').trim().split(/\s+/u).filter(Boolean).length);count();
  const onText=value=>{const html=typeof value==='string'?value:j.value;if(html===previous)return;previous=html;update('body',html);count();};j.events.on('change',onText);setEditor(j);
  return()=>{j.events.off('change',onText);j.destruct();mount.current.replaceChildren();};
 },[]);
 useEffect(()=>{editor?.setReadOnly(!initial.editable||busy);},[editor,busy]); const save=async(auto=false)=>{
  if(saving.current||!changed.current)return;
  saving.current=true;setBusy(true);setMessage('Menyimpan…');const snapshot={...current.current,autosave:auto};
  try{const response=await fetch(`/admin/articles/${initial.id}`,{method:'PUT',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content},body:JSON.stringify(snapshot)});const result=await response.json().catch(()=>({}));if(!response.ok)throw new Error(response.status===409?'Konflik versi. Salin isi Anda sebelum memuat ulang.':Object.values(result.errors??{}).flat().join(' ')||result.message||'Penyimpanan gagal. Coba lagi.');const unchanged=Object.keys(snapshot).filter(k=>!['autosave','version'].includes(k)).every(k=>current.current[k]===snapshot[k]);current.current={...current.current,version:result.version};setData({...current.current});document.querySelectorAll('input[name="version"]').forEach(input=>{input.value=result.version;});if(unchanged){changed.current=false;setDirty(false);}setMessage(`Tersimpan ${new Date().toLocaleTimeString('id-ID')}`);if(!auto)window.location.reload();}catch(error){setMessage(error.message);}finally{saving.current=false;setBusy(false);}
 };
 useEffect(()=>{const warn=e=>{if(changed.current){e.preventDefault();e.returnValue='';}};window.addEventListener('beforeunload',warn);const timer=setInterval(()=>{if(changed.current&&['draft','needs_revision'].includes(initial.status))save(true);},30000);return()=>{clearInterval(timer);window.removeEventListener('beforeunload',warn);};},[]);
 useEffect(()=>{if(media.length)document.querySelector('[aria-label="Pilih gambar"]')?.scrollIntoView({behavior:'smooth',block:'center'});},[media.length]);
 const insertImage=m=>{if(mediaMode==='hero'){update('hero_media_id',m.id);update('hero_alt',m.alt??'');update('hero_credit',m.credit??'');update('hero_caption',m.caption??'');setMedia([]);return;}if(!editor)return;const wrapper=document.createElement('div'),p=document.createElement('p'),img=document.createElement('img'),caption=document.createElement('p');img.src=`/media/${m.id}/1280`;img.alt=m.alt??'';p.appendChild(img);caption.textContent=[m.caption,m.credit].filter(Boolean).join(' · ')||'Caption foto';wrapper.append(p,caption);editor.s.restore();editor.s.insertHTML(wrapper.innerHTML);setMedia([]);}; const field=(key,label,type='text')=><label>{label}{type==='textarea'?<textarea value={data[key]??''} onChange={e=>update(key,e.target.value)}/>:<input type={type} value={data[key]??''} onChange={e=>update(key,e.target.value)}/>}</label>;
 return <div className="newsroom-editor">
 <div className="editor-savebar"><span className={dirty?'save-state is-dirty':'save-state'} aria-live="polite"><i/>{message}</span><button type="button" onClick={()=>save(false)} disabled={!initial.editable||!dirty||busy}>{busy?'Menyimpan...':'Simpan perubahan'}</button></div>
 <div className="editor-grid"><div className="editor-canvas">
 <section className="workspace-card editor-section"><div className="section-intro"><div><h2>Naskah berita</h2><p>Mulai dengan judul yang jelas dan cerita yang bermakna.</p></div></div><fieldset disabled={!initial.editable||busy}>{field('title','Judul artikel')}{field('summary','Ringkasan berita','textarea')}<label>Isi berita</label><div ref={mount} className="jodit-newsroom"/><div className="writing-stats"><span>{words.toLocaleString('id-ID')} kata</span><span>{Math.max(1,Math.ceil(words/200))} menit baca</span><span>Ctrl+B tebal &middot; Ctrl+I miring</span></div></fieldset></section>
 {media.length>0&&<section className="workspace-card editor-section" role="region" aria-label="Pilih gambar"><div className="section-intro"><h2>{mediaMode==='hero'?'Pilih gambar utama':'Sisipkan gambar'}</h2><button className="button-secondary" type="button" onClick={()=>setMedia([])}>Tutup</button></div><div className="media-picker">{media.map(m=><button className="media-choice" type="button" key={m.id} onClick={()=>insertImage(m)}><img src={`/media/${m.id}/480`} alt={m.alt}/><span>{m.name}</span></button>)}</div></section>}
 <section className="workspace-card editor-section"><div className="section-intro"><div><h2>Gambar utama</h2><p>Gambar pembuka yang tampil bersama artikel.</p></div></div><fieldset disabled={!initial.editable||busy}><div className="editor-hero-picker">{data.hero_media_id?<img src={`/media/${data.hero_media_id}/480`} alt={data.hero_alt||''}/>:<span>Belum ada gambar utama</span>}<div><button type="button" className="button-secondary" onClick={()=>loadMedia('hero')}>Pilih dari pustaka media</button><a className="text-link" href="/admin/media" target="_blank" rel="noopener">Unggah gambar baru &rarr;</a></div></div><div className="form-grid">{field('hero_alt','Deskripsi gambar (alt)')}{field('hero_credit','Kredit / sumber')}{field('hero_caption','Caption gambar','textarea')}</div></fieldset></section>
 </div><aside className="editor-sidebar">
 <section className="workspace-card editor-section"><div className="section-intro"><h2>Detail artikel</h2></div><fieldset disabled={!initial.editable||busy}><label>Topik<select value={data.category_id??''} onChange={e=>update('category_id',e.target.value)}><option value="">Pilih topik</option>{categories.map(c=><option key={c.id} value={c.id}>{c.name}</option>)}</select></label>{field('tags','Tag') }<small className="field-hint">Pisahkan tag dengan koma.</small>{field('guest_author','Penulis tamu')}{field('slug','Slug artikel')}<label className="checkbox-label"><input type="checkbox" checked={!!data.sponsored} onChange={e=>update('sponsored',e.target.checked)}/> Artikel bersponsor</label></fieldset></section>
 <section className="workspace-card editor-section"><details className="editor-disclosure"><summary>Optimasi pencarian (SEO)</summary><fieldset disabled={!initial.editable||busy}>{field('seo_title','Judul SEO')}{field('seo_description','Deskripsi SEO','textarea')}</fieldset></details></section>
 <section className="workspace-card editor-section editor-checklist"><h2>Kesiapan naskah</h2><ul>{[[data.title,'Judul artikel'],[data.summary,'Ringkasan'],[data.category_id,'Topik'],[words>0,'Isi berita'],[data.hero_media_id,'Gambar utama'],[data.hero_alt&&data.hero_credit,'Alt dan sumber foto']].map(([complete,label])=><li key={label} className={complete?'is-complete':''}><span aria-hidden="true">{complete?'✓':'○'}</span>{label}</li>)}</ul><p>Draft dan revisi disimpan otomatis setiap 30 detik.</p></section>
 </aside></div></div>;
}
const root=document.getElementById('article-editor');if(root)createRoot(root).render(<Editor initial={JSON.parse(root.dataset.article)} categories={JSON.parse(root.dataset.categories)}/>);
