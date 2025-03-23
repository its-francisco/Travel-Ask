<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FollowTag;
use App\Models\Tag;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Auth\Access\AuthorizationException;

class FollowTagController extends Controller
{

    // This function toggles the follow of a tag
    public function toggleFollow($id)
    {
        $user = Auth::user();
        $tag = Tag::findOrFail($id);
        $this->authorize('follow', FollowTag::class);

        $follow = FollowTag::where('tag_id', $id)
                           ->where('user_id', $user->id)
                           ->first();

        if ($follow != null) {
            // If user follows the tag, unfollow
            $deleted = FollowTag::where('tag_id', $id)->where('user_id', $user->id)->delete();
            return response()->json(['message' => 'Unfollowed successfully']);
        } else {
            // If the user does not follow the tag, follow
            FollowTag::create([
                'tag_id' => $id,
                'user_id' => $user->id,
            ]);
            return response()->json(['message' => 'Followed successfully']);
        }
    }

    // This function checks if the user is following a tag so that when entering the tag page, the follow button is adjusted
    public function isFollowing($id)
    {
        $user = Auth::user();
        if ($user == null) {
            return response()->json(['isFollowing' => false]);
        }
        $tag = Tag::findOrFail($id);
        $follow = FollowTag::where('tag_id', $tag->id)->where('user_id', $user->id)->first();
        return response()->json(['isFollowing' => $follow ? true : false]);
    }
}