import React, { useState } from 'react';
import { createRoot } from 'react-dom/client';

function PalettePreview() {
    const [open, setOpen] = useState(false);
    const colors = [['Sage', '#A3B18A'], ['Forest', '#344E41'], ['Ivory', '#FAF9F6'], ['Terracotta', '#BC6C4A']];
    return <section className="palette"><button aria-expanded={open} onClick={() => setOpen(!open)}>Palet desain {open ? '−' : '+'}</button>{open && <div className="swatches">{colors.map(([name, hex]) => <div key={hex}><span style={{ backgroundColor: hex }} /><strong>{name}</strong><code>{hex}</code></div>)}</div>}</section>;
}

const root = document.getElementById('palette-preview');
if (root) createRoot(root).render(<PalettePreview />);
