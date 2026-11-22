<?php
namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AvatarService
{
    public function update(User $user, ?UploadedFile $file, bool $remove = false): void
    {
        if (!$file && !$remove) return;
        $path = null;
        if ($file) {
            $source = @imagecreatefromstring(file_get_contents($file->getRealPath()));
            if (!$source) throw ValidationException::withMessages(['avatar'=>'Gambar tidak dapat dibaca.']);
            $edge=min(imagesx($source),imagesy($source));
            $canvas=imagecreatetruecolor(256,256);
            imagefill($canvas,0,0,imagecolorallocate($canvas,255,255,255));
            imagecopyresampled($canvas,$source,0,0,(int)((imagesx($source)-$edge)/2),(int)((imagesy($source)-$edge)/2),256,256,$edge,$edge);
            ob_start();imagejpeg($canvas,null,88);$bytes=ob_get_clean();
            imagedestroy($source);imagedestroy($canvas);
            $path='avatars/'.Str::uuid().'.jpg';
            if (!Storage::disk('local')->put($path,$bytes)) throw new \RuntimeException('Foto gagal disimpan.');
        }
        try {
            DB::transaction(function()use($user,$path){
                $locked=User::lockForUpdate()->findOrFail($user->id);
                $old=$locked->avatar_path;
                $locked->avatar_path=$path;$locked->save();
                $user->avatar_path=$path;
                if($old && preg_match('~^avatars/[a-zA-Z0-9-]+\.jpg$~',$old)) DB::afterCommit(fn()=>Storage::disk('local')->delete($old));
            });
        } catch(\Throwable $error) { if($path)Storage::disk('local')->delete($path);throw $error; }
    }
}
