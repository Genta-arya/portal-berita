import React, {useState,useRef,useEffect} from 'react';
import {createRoot} from 'react-dom/client';
import {Jodit} from 'jodit';
import 'jodit/esm/plugins/all.js';
import 'jodit/es2021/jodit.min.css';

function Editor({initial,categories}) {
 const [data,setData]=useState(initial),[message,setMessage]=useState('Belum ada perubahan'),[dirty,setDirty]=useState(false),[busy,setBusy]=useState(false),[editor,setEditor]=useState(null),[words,setWords]=useState(0),[mediaMode,setMediaMode]=useState('body');
 const [mediaModalOpen,setMediaModalOpen]=useState(false);
 const [mediaList,setMediaList]=useState([]);
 const [mediaPage,setMediaPage]=useState(1);
 const [mediaLastPage,setMediaLastPage]=useState(1);
 const [mediaTotal,setMediaTotal]=useState(0);
 const [mediaLoading,setMediaLoading]=useState(false);
 const [mediaSearch,setMediaSearch]=useState('');
 const [uploadingInModal,setUploadingInModal]=useState(false);
 const modalUploadInputRef=useRef(null);
 const mount=useRef(null),current=useRef(initial),changed=useRef(false),saving=useRef(false),selection=useRef(0);
 const update=(key,value)=>{
  const next={...current.current,[key]:value};
  if (key === 'title') {
    const val = value || '';
    if (!current.current.seo_title || current.current.seo_title === current.current.title) {
      next.seo_title = val;
    }
  }
  if (key === 'summary') {
    const val = value || '';
    if (!current.current.seo_description || current.current.seo_description === current.current.summary) {
      next.seo_description = val;
    }
  }
  current.current=next;setData(next);changed.current=true;setDirty(true);
 };
 const fetchMediaPage=async(page=1,query='')=>{
  setMediaLoading(true);
  try{
    const url = `/admin/media?page=${page}${query ? `&q=${encodeURIComponent(query)}` : ''}`;
    const r=await fetch(url,{headers:{Accept:'application/json'}});
    if(!r.ok)throw new Error();
    const res=await r.json();
    setMediaList(res.data||[]);
    setMediaPage(res.current_page||1);
    setMediaLastPage(res.last_page||1);
    setMediaTotal(res.total||0);
  }catch{
    setMessage('Gagal memuat pustaka media.');
  }finally{
    setMediaLoading(false);
  }
 };
 const openMediaModal=(mode='body')=>{
  setMediaMode(mode);
  setMediaModalOpen(true);
  fetchMediaPage(1, mediaSearch);
 };
 const closeMediaModal=()=>{
  setMediaModalOpen(false);
 };
 const handleMediaSearchSubmit=(e)=>{
  e.preventDefault();
  fetchMediaPage(1, mediaSearch);
 };
 useEffect(()=>{
  const host=document.createElement('textarea');host.value=initial.body??'';mount.current.appendChild(host);
  const buttons=['undo','redo','|','paragraph','bold','italic','underline','strikethrough','|','left','center','right','justify','|','ul','ol','|','link',{name:'newsMedia',icon:'image',tooltip:'Pilih gambar dari media',exec:j=>{j.s.save();openMediaModal('body');}},{name:'newsVideo',icon:'video',tooltip:'Video YouTube',exec:j=>{
    j.s.save();const url=window.prompt('URL video YouTube');if(!url)return;
    try{const u=new URL(url);const id=u.hostname==='youtu.be'?u.pathname.slice(1):['youtube.com','www.youtube.com','www.youtube-nocookie.com'].includes(u.hostname)?(u.searchParams.get('v')??u.pathname.match(/^\/embed\/([A-Za-z0-9_-]{11})$/)?.[1]):null;if(!/^[A-Za-z0-9_-]{11}$/.test(id??''))throw new Error();j.s.restore();j.s.insertHTML(`<iframe src="https://www.youtube-nocookie.com/embed/${id}" width="560" height="315" title="Video YouTube"></iframe><p><br></p>`);}catch{setMessage('Masukkan URL YouTube yang valid.');}
  }},'table','|','eraser','find','fullsize','preview','source'];
  const j=Jodit.make(host,{
    readonly:!initial.editable,
    height:500,
    minHeight:400,
    language:'en',
    sourceEditor:'area',
    spellcheck:true,
    toolbarAdaptive:false,
    toolbarSticky:true,
    buttons,
    controls:{paragraph:{list:{p:'Paragraph',h2:'Heading 2',h3:'Heading 3',blockquote:'Quote'}}},
    showCharsCounter:false,
    showWordsCounter:false,
    showXPathInStatusbar:false,
    askBeforePasteHTML:true,
    askBeforePasteFromWord:true,
    resizer:{
      showSize:true,
      hideSizeTimeout:1000,
      forImageChangeAttributes:true,
      min_width:30,
      min_height:30
    },
    popup:{
      img:[
        {
          name:'align',
          icon:'left',
          list:['Left','Center','Right','Normal'],
          tooltip:'Rata kiri / tengah / kanan',
          exec:(editor,image,{control})=>{
            const align=(control.args?.[0]||'').toLowerCase();
            if(!image)return;
            if(align==='center'){
              image.style.display='block';
              image.style.marginLeft='auto';
              image.style.marginRight='auto';
              image.style.float='none';
            }else if(align==='left'){
              image.style.display='inline-block';
              image.style.float='left';
              image.style.marginRight='16px';
              image.style.marginBottom='10px';
              image.style.marginLeft='0';
            }else if(align==='right'){
              image.style.display='inline-block';
              image.style.float='right';
              image.style.marginLeft='16px';
              image.style.marginBottom='10px';
              image.style.marginRight='0';
            }else{
              image.style.display='block';
              image.style.float='none';
              image.style.margin='14px auto';
            }
            editor.synchronizeValues();
            editor.events.fire('recalcPositionPopup');
          }
        },
        '|',
        {
          name:'edit',
          icon:'pencil',
          tooltip:'Pengaturan gambar',
          exec:(editor,image)=>{
            if(image)editor.events.fire('openImageProperties',image);
          }
        },
        '|',
        {
          name:'delete',
          icon:'bin',
          tooltip:'Hapus gambar',
          exec:(editor,image)=>{
            if(image)editor.s.removeNode(image);
          }
        }
      ]
    },
    disablePlugins:['file','filebrowser','video','drag-and-drop','drag-and-drop-element','paste-storage']
  });
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
  const insertImage=m=>{
  if(mediaMode==='hero'){
    update('hero_media_id',m.id);
    update('hero_alt',m.alt??'');
    update('hero_credit',m.credit??'');
    update('hero_caption',m.caption??'');
    closeMediaModal();
    return;
  }
  if(!editor)return;
  const wrapper=document.createElement('div'),p=document.createElement('p'),img=document.createElement('img'),caption=document.createElement('p');
  img.src=`/media/${m.id}/1280`;
  img.alt=m.alt??'';
  p.appendChild(img);
  caption.textContent=[m.caption,m.credit].filter(Boolean).join(' · ')||'Caption foto';
  wrapper.append(p,caption);
  editor.s.restore();
  editor.s.insertHTML(wrapper.innerHTML);
  closeMediaModal();
 };

 const uploadFromModal = async (file) => {
   if (!file) return;
   setUploadingInModal(true);
   setMessage('Mengunggah gambar ke pustaka media...');
   const formData = new FormData();
   formData.append('image', file);
   const autoAlt = file.name.replace(/\.[^/.]+$/, "");
   formData.append('alt', autoAlt);
   formData.append('credit', 'Redaksi');
   try {
     const res = await fetch('/admin/media', {
       method: 'POST',
       headers: {
         'Accept': 'application/json',
         'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
       },
       body: formData
     });
     if (!res.ok) {
       const err = await res.json().catch(() => ({}));
       throw new Error(Object.values(err.errors || {}).flat().join(' ') || err.message || 'Gagal mengunggah gambar.');
     }
     const uploaded = await res.json();
     setMessage('Gambar berhasil diunggah ke pustaka.');
     await fetchMediaPage(1, mediaSearch);
     if (mediaMode === 'hero') {
       update('hero_media_id', uploaded.id);
       update('hero_alt', autoAlt);
       update('hero_credit', 'Redaksi');
       closeMediaModal();
     }
   } catch (err) {
     setMessage(err.message);
   } finally {
     setUploadingInModal(false);
     if (modalUploadInputRef.current) modalUploadInputRef.current.value = '';
   }
 };

 const field=(key,label,type='text')=><label>{label}{type==='textarea'?<textarea value={data[key]??''} onChange={e=>update(key,e.target.value)}/>:<input type={type} value={data[key]??''} onChange={e=>update(key,e.target.value)}/>}</label>;
 const fileInputRef = useRef(null);
 const uploadDirect = async (file) => {
   if (!file) return;
   setBusy(true);
   setMessage('Mengunggah gambar...');
   const formData = new FormData();
   formData.append('image', file);
   const autoAlt = (data.title || '').trim() || file.name.replace(/\.[^/.]+$/, "");
   const autoCredit = (data.hero_credit || '').trim() || 'Redaksi';
   formData.append('alt', autoAlt);
   formData.append('credit', autoCredit);
   try {
     const res = await fetch('/admin/media', {
       method: 'POST',
       headers: {
         'Accept': 'application/json',
         'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
       },
       body: formData
     });
     if (!res.ok) {
       const err = await res.json().catch(() => ({}));
       throw new Error(Object.values(err.errors || {}).flat().join(' ') || err.message || 'Gagal mengunggah gambar.');
     }
     const uploaded = await res.json();
     update('hero_media_id', uploaded.id);
     update('hero_alt', autoAlt);
     update('hero_credit', autoCredit);
     setMessage('Gambar utama berhasil diunggah.');
   } catch (err) {
     setMessage(err.message);
   } finally {
     setBusy(false);
     if (fileInputRef.current) fileInputRef.current.value = '';
   }
 };

 return <div className="newsroom-editor">
 <div className="editor-savebar"><span className={dirty?'save-state is-dirty':'save-state'} aria-live="polite"><i/>{message}</span><button type="button" onClick={()=>save(false)} disabled={!initial.editable||!dirty||busy}>{busy?'Menyimpan...':'Simpan perubahan'}</button></div>
 <div className="editor-grid"><div className="editor-canvas">
 <section className="workspace-card editor-section"><div className="section-intro"><div><h2>Naskah berita</h2><p>Mulai dengan judul yang jelas dan cerita yang bermakna.</p></div></div><fieldset disabled={!initial.editable||busy}>{field('title','Judul artikel')}{field('summary','Ringkasan berita','textarea')}<label>Isi berita</label><div ref={mount} className="jodit-newsroom"/><div className="writing-stats"><span>{words.toLocaleString('id-ID')} kata</span><span>{Math.max(1,Math.ceil(words/200))} menit baca</span><span>Ctrl+B tebal &middot; Ctrl+I miring</span></div></fieldset></section>
 <section className="workspace-card editor-section"><div className="section-intro"><div><h2>Gambar utama</h2><p>Gambar pembuka yang tampil bersama artikel.</p></div></div><fieldset disabled={!initial.editable||busy}>
   <div className="editor-hero-card">
     <div className="editor-hero-preview">
       {data.hero_media_id ? (
         <img src={`/media/${data.hero_media_id}/480`} alt={data.hero_alt||''} />
       ) : (
         <div className="editor-hero-placeholder">
           <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>
           <span>Belum ada gambar terpilih</span>
         </div>
       )}
     </div>
     <div className="editor-hero-actions">
       <input type="file" ref={fileInputRef} accept="image/*" style={{display:'none'}} onChange={e=>uploadDirect(e.target.files?.[0])}/>
       <button type="button" className="button editor-upload-btn" onClick={()=>fileInputRef.current?.click()}>
         <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
         Upload Gambar Langsung
       </button>
       <button type="button" className="button-secondary editor-library-btn" onClick={()=>openMediaModal('hero')}>
         <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>
         Pilih dari pustaka
       </button>
       <p className="editor-hero-tip">Format JPG, PNG, atau WEBP. Maks 10 MB.</p>
     </div>
   </div>
   <div className="form-grid">{field('hero_alt','Deskripsi gambar (alt - otomatis dari judul)')}{field('hero_credit','Kredit / sumber (otomatis Redaksi)')}{field('hero_caption','Caption gambar (opsional)','textarea')}</div>
 </fieldset></section>
 </div><aside className="editor-sidebar">
 <section className="workspace-card editor-section"><div className="section-intro"><h2>Detail artikel</h2></div><fieldset disabled={!initial.editable||busy}><label>Topik<select value={data.category_id??''} onChange={e=>update('category_id',e.target.value)}><option value="">Pilih topik</option>{categories.map(c=><option key={c.id} value={c.id}>{c.name}</option>)}</select></label>{field('tags','Tag') }<small className="field-hint">Pisahkan tag dengan koma.</small>{field('guest_author','Penulis tamu')}{field('slug','Slug artikel')}<label className="checkbox-label"><input type="checkbox" checked={!!data.sponsored} onChange={e=>update('sponsored',e.target.checked)}/> Artikel bersponsor</label></fieldset></section>
 <section className="workspace-card editor-section"><details className="editor-disclosure"><summary>Optimasi pencarian (SEO)</summary><fieldset disabled={!initial.editable||busy}>{field('seo_title','Judul SEO (otomatis dari judul)')}{field('seo_description','Deskripsi SEO (otomatis dari ringkasan)','textarea')}</fieldset></details></section>
 <section className="workspace-card editor-section editor-checklist"><h2>Kesiapan naskah</h2><ul>{[[data.title,'Judul artikel'],[data.summary,'Ringkasan'],[data.category_id,'Topik'],[words>0,'Isi berita'],[data.hero_media_id,'Gambar utama'],[data.hero_alt&&data.hero_credit,'Alt dan sumber foto']].map(([complete,label])=><li key={label} className={complete?'is-complete':''}><span aria-hidden="true">{complete?'✓':'○'}</span>{label}</li>)}</ul><p>Draft dan revisi disimpan otomatis setiap 30 detik.</p></section>
 </aside></div>

 {mediaModalOpen && (
   <div className="media-modal-backdrop" onClick={closeMediaModal}>
     <div className="media-modal-container" onClick={e=>e.stopPropagation()}>
       <div className="media-modal-header">
         <div className="media-modal-title">
           <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" strokeWidth="2"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>
           <div>
             <h3>{mediaMode==='hero' ? 'Pilih Gambar Utama' : 'Sisipkan Gambar ke Naskah'}</h3>
             <p>{mediaTotal > 0 ? `${mediaTotal} gambar tersedia di pustaka` : 'Pustaka media'}</p>
           </div>
         </div>
         <div className="media-modal-actions">
           <input type="file" ref={modalUploadInputRef} accept="image/jpeg,image/png,image/webp" style={{display:'none'}} onChange={e=>uploadFromModal(e.target.files?.[0])}/>
           <button type="button" className="button media-modal-upload-btn" disabled={uploadingInModal} onClick={()=>modalUploadInputRef.current?.click()}>
             <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
             {uploadingInModal ? 'Mengunggah...' : 'Unggah Gambar Baru'}
           </button>
           <button type="button" className="icon-button media-modal-close-btn" onClick={closeMediaModal} aria-label="Tutup">
             <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2"><path d="M18 6 6 18M6 6l12 12"/></svg>
           </button>
         </div>
       </div>

       <div className="media-modal-toolbar">
         <form className="media-modal-search" onSubmit={handleMediaSearchSubmit}>
           <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" strokeWidth="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
           <input type="text" placeholder="Cari nama gambar di pustaka..." value={mediaSearch} onChange={e=>setMediaSearch(e.target.value)} />
           {mediaSearch && (
             <button type="button" className="media-modal-clear-search" onClick={()=>{setMediaSearch(''); fetchMediaPage(1, '');}}>
               &times;
             </button>
           )}
           <button type="submit" className="button-secondary media-modal-search-btn">Cari</button>
         </form>
         <span className="media-modal-hint">Klik pada gambar untuk langsung memilihnya.</span>
       </div>

       <div className="media-modal-body">
         {mediaLoading ? (
           <div className="media-modal-loading">
             <div className="media-modal-spinner" />
             <span>Memuat pustaka media...</span>
           </div>
         ) : mediaList.length === 0 ? (
           <div className="media-modal-empty">
             <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>
             <h4>Tidak ada gambar ditemukan</h4>
             <p>{mediaSearch ? `Tidak ada hasil pencarian untuk "${mediaSearch}". Coba kata kunci lain.` : 'Belum ada gambar yang diunggah. Unggah gambar sekarang.'}</p>
             <button type="button" className="button" onClick={()=>modalUploadInputRef.current?.click()}>Unggah Gambar Sekarang</button>
           </div>
         ) : (
           <div className="media-modal-grid">
             {mediaList.map(m => (
               <button type="button" key={m.id} className="media-modal-card" onClick={()=>insertImage(m)} title={`Pilih ${m.name || 'Gambar'}`}>
                 <div className="media-modal-card-thumb">
                   <img src={`/media/${m.id}/480`} alt={m.alt || m.name} loading="lazy" />
                   <span className="media-modal-card-badge">#{m.id}</span>
                 </div>
                 <div className="media-modal-card-info">
                   <strong title={m.name}>{m.name || 'Tanpa nama'}</strong>
                   <small>{m.width && m.height ? `${m.width} × ${m.height} px` : ''} {m.size ? `· ${Math.round(m.size/1024)} KB` : ''}</small>
                 </div>
               </button>
             ))}
           </div>
         )}
       </div>

       {mediaLastPage > 1 && (
         <div className="media-modal-footer">
           <div className="media-modal-pagination">
             <button type="button" className="pagination-step" disabled={mediaPage <= 1 || mediaLoading} onClick={()=>fetchMediaPage(mediaPage - 1, mediaSearch)}>
               &larr; Sebelumnya
             </button>
             <div className="media-modal-pages">
               {Array.from({length: mediaLastPage}, (_, i) => i + 1)
                 .filter(p => p === 1 || p === mediaLastPage || (p >= mediaPage - 2 && p <= mediaPage + 2))
                 .reduce((acc, p, idx, arr) => {
                   if (idx > 0 && p - arr[idx - 1] > 1) acc.push('...');
                   acc.push(p);
                   return acc;
                 }, [])
                 .map((p, idx) => (
                   p === '...' ? (
                     <span key={`ellipsis-${idx}`} className="pagination-ellipsis">&hellip;</span>
                   ) : (
                     <button key={p} type="button" className={`pagination-number ${p === mediaPage ? 'is-current' : ''}`} disabled={mediaLoading} onClick={()=>fetchMediaPage(p, mediaSearch)}>
                       {p}
                     </button>
                   )
                 ))
               }
             </div>
             <button type="button" className="pagination-step" disabled={mediaPage >= mediaLastPage || mediaLoading} onClick={()=>fetchMediaPage(mediaPage + 1, mediaSearch)}>
               Berikutnya &rarr;
             </button>
           </div>
           <span className="media-modal-page-info">Halaman {mediaPage} dari {mediaLastPage}</span>
         </div>
       )}
     </div>
   </div>
 )}
 </div>;
}
const root=document.getElementById('article-editor');if(root)createRoot(root).render(<Editor initial={JSON.parse(root.dataset.article)} categories={JSON.parse(root.dataset.categories)}/>);
