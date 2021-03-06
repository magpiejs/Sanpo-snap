<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\User;

class UserEditController extends Controller
{
    /**
     * @var string icon store directory
     */
    private $icon_dir = 'avatar';

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        $user = User::find(auth()->id());
        if ($user === null) {
            abort(404);


        }

        return view('user_edit', [
                'user' => $user,
                'image_dir' => '/storage/' . $this->icon_dir . '/',
            ]
        );
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request) {
        $user = User::find($request->id);
        $this->validator($user, $request->all())->validate();
        $this->store($user, $request);
        return redirect()->back()->withInput()->with('success', __('プロフィールを更新しました。'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id) {
        $target_user = User::find($id);
        if ($target_user === null) {
            abort(404);
        }

        $follows = $target_user->follows->count();
        $followers = $target_user->followers->count();

        $is_following = false;
        $auth_user = User::find(auth()->id());
        if ($auth_user !== null) {
            $is_following = $target_user->followers->where('follower_id', $auth_user->id)->first() !== null;
        }

        return view('user_show', [
            'user' => $target_user,
            'follows_count' => $follows,
            'followers_count' => $followers,
            'is_following' => $is_following,
            'auth_user' => $auth_user,
        ]);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator($user, array $data) {
        return Validator::make($data, [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'comment' => 'string|max:255|nullable',
        ]);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function store($user, Request $request) {
        // update avator image
        if ($request->image_uploaded === "1") {
            if ($user->icon_file !== null) { // remove old image if exist
                Storage::delete($this->build_image_path($user->icon_file));
            }
            $tmp_dir = config('app.image_tmp_dir');
            Storage::move("{$tmp_dir}/{$request->image_filename}", $this->build_image_path($request->image_filename));
            $user->icon_file = $request->image_filename;
        } else if ($request->image_filename !== $user->icon_file) { // remove image
            Storage::delete($this->build_image_path($user->icon_file));
            $user->icon_file = null;
        }

        $user->name = $request->name;
        $user->email = $request->email;
        $user->comment = $request->comment;
        $user->save();
    }

    /**
     * @param $filename
     */
    private function build_image_path($filename) {
        return "public/{$this->icon_dir}/{$filename}";
    }
}
