document.querySelectorAll('[data-avatar-upload]').forEach(widget => {
    const file=widget.querySelector('[data-avatar-input]');
    const preview=widget.querySelector('[data-avatar-preview]');
    const fallback=widget.querySelector('[data-avatar-fallback]');
    const remove=widget.querySelector('[data-avatar-remove]');
    const removeLabel=widget.querySelector('[data-avatar-remove-label]');
    let current=widget.dataset.avatarCurrent || '', objectUrl;
    const release=()=>{if(objectUrl){URL.revokeObjectURL(objectUrl);objectUrl=null;}};
    const render=()=>{
        release();
        const selected=file.files[0];
        let source=remove.checked ? '' : current;
        if(selected){objectUrl=URL.createObjectURL(selected);source=objectUrl;}
        preview.hidden=!source;fallback.hidden=!!source;
        if(source)preview.src=source;else preview.removeAttribute('src');
        removeLabel.hidden=!current;
    };
    widget.addEventListener('avatar:reset',event=>{current=event.detail?.url || '';file.value='';remove.checked=false;render();});
    file.addEventListener('change',()=>{if(file.files[0])remove.checked=false;render();});
    remove.addEventListener('change',render);
    window.addEventListener('pagehide',release);
    render();
});
