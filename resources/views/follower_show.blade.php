@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row d-flex justify-content-center">
            <div class="col-md-12">
                <div class="card border-0">
                    <div class="card-header border-0">{{ __('フォローされているユーザー') }}</div>
                    <div class="card-body mx-auto">
                        @foreach ($follows as $user)
                            <user-panel
                                :id="{{$user->id}}"
                                name="{{$user->name}}"
                                icon-url="{{ $user->icon_file ? asset("storage/avatar/{$user->icon_file}") : '' }}"
                                :is-following={{ json_encode($user->is_following != null)}}
                                :button-type={{ ($auth_user == null || $auth_user->id == $user->id) ? 0 : ($user->is_following != null ?  2 : 1)}}
                            >
                            </user-panel>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
