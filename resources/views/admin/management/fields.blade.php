<div class="form-grid">
@if($module==='users')@include('admin.avatar-field')@endif
@if(in_array($module,['users','categories','tags']))
    <label @class(['field-full'=>$module!=='users'])>Nama {{ $module==='users' ? 'lengkap' : mb_strtolower($title) }}<input name="name" value="{{ old('name') }}" required maxlength="255" placeholder="{{ $module==='users' ? 'Nama anggota redaksi' : 'Nama '.mb_strtolower($title) }}"></label>
@endif
@if(in_array($module,['categories','tags','pages']))
    @if($module==='pages')<label class="field-full">Judul halaman<input name="title" value="{{ old('title') }}" placeholder="Contoh: Tentang Kami" required maxlength="255"></label>@endif
    <label class="field-full">Slug / alamat halaman<input name="slug" value="{{ old('slug') }}" required placeholder="contoh-alamat" pattern="[A-Za-z0-9_-]+"><small>Gunakan huruf, angka, dan tanda hubung. Alamat: /{{ $module==='pages' ? 'halaman' : ($module==='tags' ? 'tag' : 'kanal') }}/<span data-slug-preview>contoh-alamat</span></small></label>
@endif
@if($module==='categories')<label class="field-full">Deskripsi<textarea name="description" rows="3" placeholder="Jelaskan cakupan berita dalam topik ini">{{ old('description') }}</textarea></label><label>Urutan tampilan<input name="position" type="number" min="0" value="{{ old('position',0) }}"></label>@endif
@if($module==='users')
    <label>Email<input name="email" type="email" required value="{{ old('email') }}" placeholder="nama@redaksi.com"></label>
    <label>Peran<select name="role">@foreach((auth()->user()->isDeveloper() ? ['penulis'=>'Penulis','editor'=>'Editor','admin'=>'Admin'] : ['penulis'=>'Penulis','editor'=>'Editor']) as $value=>$label)<option value="{{ $value }}" @selected(old('role')===$value)>{{ $label }}</option>@endforeach</select></label>
    <label>Kata sandi<div class="account-password-field"><input id="account-password" name="password" type="password" autocomplete="new-password" minlength="8" @required(!old('id')) placeholder="Minimal 8 karakter"><button type="button" class="password-reveal" data-reveal-password="account-password" aria-controls="account-password" aria-label="Tampilkan kata sandi" aria-pressed="false">@include('admin.icon',['name'=>'eye'])</button></div><small>Wajib untuk akun baru. Saat edit, kosongkan jika tidak ingin menggantinya.</small></label>
    <label>Konfirmasi kata sandi<div class="account-password-field"><input id="account-password-confirmation" name="password_confirmation" type="password" autocomplete="new-password" minlength="8" @required(!old('id')) placeholder="Ulangi kata sandi"><button type="button" class="password-reveal" data-reveal-password="account-password-confirmation" aria-controls="account-password-confirmation" aria-label="Tampilkan konfirmasi kata sandi" aria-pressed="false">@include('admin.icon',['name'=>'eye'])</button></div></label>
    <label class="field-full">Bio singkat<textarea name="bio" rows="3" placeholder="Profil singkat anggota redaksi">{{ old('bio') }}</textarea></label>
@endif
@if($module==='pages')
    <div class="field-full page-body-field"><div class="page-editor-heading"><label for="page-body">Isi halaman</label><div class="page-editor-modes" role="group" aria-label="Mode editor halaman"><button type="button" data-page-mode="visual" aria-pressed="true">Visual</button><button type="button" data-page-mode="html" aria-pressed="false"><span aria-hidden="true">&lt;/&gt;</span> HTML</button></div></div><textarea id="page-body" name="body" rows="14" required data-page-editor>{{ old('body') }}</textarea><small>Gunakan mode Visual untuk menyusun isi atau mode HTML untuk mengedit kode.</small></div>
@endif
@if($module==='trending')
    <label class="field-full">Topik<select name="category_id" required><option value="">Pilih topik</option>@foreach($topicOptions as $topic)<option value="{{ $topic->id }}" @selected(old('category_id')==$topic->id)>{{ $topic->name }}{{ !$topic->active ? ' (nonaktif)' : '' }}</option>@endforeach</select><small>Hanya topik aktif yang memiliki artikel terbit yang tampil di beranda.</small></label><label>Urutan tampilan<input name="position" type="number" min="1" max="99" value="{{ old('position',1) }}" required></label>
@endif
@if($module==='featured')
    <label class="field-full">Pilih artikel terbit<select name="article_id" required><option value="">Pilih berita yang ingin ditampilkan</option>@foreach($publishedArticles as $item)<option value="{{ $item->id }}" @selected(old('article_id')==$item->id)>{{ $item->title }}</option>@endforeach</select></label>
    <label>Penempatan<select name="location"><option value="headline" @selected(old('location')==='headline')>Headline</option><option value="pick" @selected(old('location')==='pick')>Pilihan redaksi</option></select></label><label>Urutan tampilan<input name="position" type="number" min="1" max="20" value="{{ old('position',1) }}" required><small>Urutan yang sama akan menggantikan pilihan sebelumnya.</small></label>
@endif
@if(in_array($module,['banners','breaking']))
    @if($module==='banners')
        <label>Penempatan<select name="slot">@foreach(['home_top'=>'Beranda atas','list_middle'=>'Di antara berita','sidebar'=>'Sidebar','article_bottom'=>'Bawah artikel'] as $value=>$label)<option value="{{ $value }}" @selected(old('slot')===$value)>{{ $label }}</option>@endforeach</select></label>
        <label>Gambar iklan<select name="media_id" required><option value="">Pilih dari pustaka media</option>@foreach($mediaOptions as $media)<option value="{{ $media->id }}" @selected(old('media_id')==$media->id)>{{ $media->name }} (#{{ $media->id }})</option>@endforeach</select></label>
        <label class="field-full">Deskripsi gambar<input name="alt" value="{{ old('alt') }}" required maxlength="255" placeholder="Deskripsi singkat materi iklan"></label>
    @else<label class="field-full">Teks kabar penting<textarea name="text" maxlength="255" rows="3" required placeholder="Tulis kabar penting untuk ticker berita">{{ old('text') }}</textarea></label>@endif
    <label class="field-full">Tautan tujuan<input name="url" type="url" value="{{ old('url') }}" placeholder="https://..." @required($module==='banners')></label>
    <label>Mulai tayang (WIB)<input name="starts_at" type="datetime-local" value="{{ old('starts_at') }}" required></label><label>Berakhir (WIB)<input name="ends_at" type="datetime-local" value="{{ old('ends_at') }}" required></label>
@endif
@if($module!=='featured')<label>Status<select name="active"><option value="1" @selected(old('active','1')=='1')>Aktif</option><option value="0" @selected(old('active')==='0')>Nonaktif</option></select></label>@endif
</div>
