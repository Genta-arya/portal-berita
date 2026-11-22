<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AvatarService;
use App\Services\EditorialService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function edit(Request $request) { return view('admin.profile',['profile'=>$request->user()]); }
    public function update(Request $request, AvatarService $avatars, EditorialService $editorial)
    {
        $request->validate(['avatar'=>'nullable|image|mimes:jpg,jpeg,png,webp|max:2048|dimensions:max_width=4000,max_height=4000','remove_avatar'=>'sometimes|boolean']);
        $avatars->update($request->user(),$request->file('avatar'),$request->boolean('remove_avatar'));
        $editorial->audit($request->user(),'profile_photo_update','user',$request->user()->id);
        return back()->with('success','Foto profil diperbarui.');
    }
    public function avatar(User $user)
    {
        abort_unless($user->avatar_path && Storage::disk('local')->exists($user->avatar_path),404);
        return response()->file(Storage::disk('local')->path($user->avatar_path),['Content-Type'=>'image/jpeg','Cache-Control'=>'private, no-store','X-Content-Type-Options'=>'nosniff']);
    }
    public function publicAvatar(User $user)
    {
        abort_unless($user->avatar_path && Storage::disk('local')->exists($user->avatar_path), 404);
        return response()->file(Storage::disk('local')->path($user->avatar_path), [
            'Content-Type' => 'image/jpeg',
            'Cache-Control' => 'public, max-age=86400',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
