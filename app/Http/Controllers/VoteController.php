<?php

namespace App\Http\Controllers;

use App\Models\Vote;
use Illuminate\Http\Request;
use App\Models\Question;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Auth\Access\AuthorizationException;


class VoteController extends Controller
{
    public function vote(Request $request, $id)
    {
        $user = Auth::user();
        $post = Post::findOrFail($id);
        $this->authorize('create', Vote::class);
        $this->authorize('checkOwner', [Vote::class, $post]);

        // Verificar se o user já votou
        $existingVote = Vote::where('post_id', $id)
                            ->where('user_id', $user->id)
                            ->first();

        // Tudo o que envolve deletes não funciona?!?!?!?!
        // Se for save já dá...

        if ($existingVote && $existingVote->vote == $request->input('vote')) {
            // Remover o voto existente
            $deleted = Vote::where('post_id', $id)
                            ->where('user_id', $user->id)->delete();
            return response()->json(['message' => 'Vote removed successfully']);
        }
        elseif ($existingVote) {
            // Apagar o antigo e criar um novo
            $deleted = Vote::where('post_id', $id)
                            ->where('user_id', $user->id)->delete();
            $vote = new Vote();
            $vote->post_id = $id;
            $vote->user_id = $user->id;
            $vote->vote = $request->input('vote');
            $vote->save();
            return response()->json(['message' => 'Vote updated successfully']);

        }
        else {
            // Criar um novo voto
            $vote = new Vote();
            $vote->post_id = $id;
            $vote->user_id = $user->id;
            $vote->vote = $request->input('vote');
            $vote->save();
            $question = Question::where('post_id', $post->id)->first();
            if ($question) {
                NotificationController::sendVoteNotification($id);
            }
            return response()->json(['message' => 'Vote added successfully']);
        }

    }

    public function unvote(Vote $vote)
    {
        $this->authorize('delete', $vote);
        $vote->delete();
        return;
    }
}
