<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Poll extends Model
{
    use HasFactory;
    
    protected $guarded = [];
    protected $hidden = ['updated_at'];
    protected $appends = ['result', 'creator'];
    protected $with = ['choices'];

    public function getResultAttribute() {
        if (!$this->canViewResult()) {
            return null;
        }
        
        return $this->pollResult();
    }

    public function pollResult() {
        $choices = $this->choices()->get()->keyBy('id')->all();
        foreach($choices as &$choice) {
            $choice['point'] = 0;
        }

        $divisions = Division::all();
        foreach ($divisions as $division) {
            $winners = $this->divisionResult($division);
            if(count($winners) == 0) continue;

            $point = 1/count($winners);
            foreach($winners as $id) {
                $choices[$id]['point'] += $point;
            }
        }

        return collect($choices)->flatten()->all();
    }

    public function divisionResult(Division $division) {
        $votes = Vote::where('poll_id', $this->id)
            ->groupBy('choice_id')
            ->where('division_id', $division->id)
            ->select('choice_id', DB::raw('count(1) as total'))
            ->orderBy(DB::raw('count(1)', 'DESC'))
            ->get();

        if(count($votes) == 0) return [];

        $max = $votes[0]['total'];
        $ids = [$votes[0]['choice_id']];

        for($i = 1; $i < count($votes); $i++) {
            $vote = $votes[$i];
            if($vote['total'] != $max) break;
            array_push($ids, $vote['choice_id']);
        }

        return $ids;
    }

    public function getCreatorAttribute() {
        return User::find($this->created_by)->username;
    }

    public function choices() {
        return $this->hasMany(Choice::class);
    }

    public function isDeadline() {
        return $this->deadline < Carbon::now();
    }

    public function isVoted() {
        $user = Auth::user();
        $vote = Vote::where('user_id', $user->id)->where('poll_id', $this->id)->count();

        return $vote > 0;
    }

    public function canViewResult() {
        return $this->isDeadline() || $this->isVoted() || Auth::user()->role == 'admin';
    }
}
