<?php

namespace App\Http\Controllers;

use App\Models\Choice;
use App\Models\Poll;
use App\Models\Vote;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PollController extends Controller
{
    public function index()
    {
        if (!Auth::user()) return response()->json(['message' => 'Unauthorized'], 401);

        return Poll::all()->sortByDesc('created_at')->flatten();
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user || $user->role != 'admin') return response()->json(['message' => 'Unauthorized'], 401);
        
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'description' => 'required|string',
            'deadline' => 'required|date',
            'choices' => 'required|array|min:2',
            'choices.*' => 'required|string|distinct'
        ]);
        
        if ($validator->fails()) {
            return response()->json(['message' => 'The given data was invalid.'], 422);
        }

        $poll = Poll::create([
            'title' => $request->title,
            'description' => $request->description,
            'deadline' => $request->deadline,
            'created_by' => $user->id
        ]);
        
        foreach ($request->choices as $choice) {
            Choice::create([
                'choice' => $choice,
                'poll_id' => $poll->id
            ]);
        }
        
        return $poll;
    }
    
    public function show($id)
    {
        if (!Auth::user()) return response()->json(['message' => 'Unauthorized'], 401);

        return Poll::find($id);
    }
    
    public function destroy(Poll $poll)
    {
        $user = Auth::user();
        if (!$user || $user->role != 'admin') return response()->json(['message' => 'Unauthorized'], 401);

        Choice::where('poll_id', $poll->id)->delete();

        $poll->delete();

        return;
    }

    public function vote($pollId, $choiceId)
    {
        // invalid choice
        $choice = Choice::where('poll_id', $pollId)->where('id', $choiceId)->first();
        if (!$choice) {
            return response()->json(['message' => 'invalid choice'], 422);
        }

        // is user
        $user = Auth::user();
        if (!$user || $user->role != 'user') {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $poll = Poll::find($pollId);

        // alerady voted
        if ($poll->isVoted()) {
            return response()->json(['message' => 'already voted'], 422);
        }

        // voting deadline
        if ($poll->isDeadline()) {
            return response()->json(['message' => 'voting deadline'], 422);
        } 

        Vote::create([
            'choice_id' => $choiceId,
            'user_id' => $user->id,
            'poll_id' => $pollId,
            'division_id' => $user->division_id
        ]);

        return response()->json(['message' => 'voting success'], 200);
    }
}
