<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\User;
use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;

class ProfileController extends Controller
{
    public function getProfile($username)
    {
        $user = User::where('username', $username)->first();

        if (!$user) abort(404);

            $statuses = $user->statuses()->notReply()->get();


        return view('profile.index', [
            'user' => $user,
            'statuses' => $statuses,
            'authUserIsFriend' => Auth::user()->isFriendWith($user)
        ]);
    }

    public function getEdit()
    {
        return view('profile.edit');
    }

    public function postEdit(Request $request)
    {
        $this->validate($request, [
            'first_name' => 'alpha|max:50',
            'last_name' => 'alpha|max:50',
            'location' => 'alpha|max:20'
        ]);

        Auth::user()->update([
            'first_name' => $request->input('first_name'),
            'last_name' => $request->input('last_name'),
            'location' => $request->input('location')
        ]);

        return redirect()
            ->route('profile.edit')
            ->with('info', 'Профиль успешно обновлен!');
    }

    public function postUploadAvatar(Request $request, $username)
    {
        $user = User::where('username', $username)->first();

        if (!Auth::user()->id === $user->id){
            return redirect()->route('home');
        }

        if ($request->hasFile('avatar')){
            $user->clearAvatars($user->id);
            $avatar = $request->file('avatar');
            $filename = time() . '.' . $avatar->getClientOriginalExtension();

            Image::make($avatar)->resize(100, 100)
                ->save(public_path($user->getAvatarsPath($user->id)) . $filename);

            $user = Auth::user();
            $user->avatar = $filename;
            $user->save();

        }

        return redirect()->back();
    }

}
