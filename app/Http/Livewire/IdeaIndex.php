<?php

namespace App\Http\Livewire;

use App\Exceptions\DuplicateVoteException;
use App\Exceptions\VoteNotFoundException;
use App\Models\Idea;
use Livewire\Component;

class IdeaIndex extends Component
{
    public $idea;
    public $votesCount;
    public $hasVoted;

    public function mount(Idea $idea, $votesCount){
        $this->idea=$idea;
        $this->votesCount=$votesCount;
        $this->hasVoted=$idea->voted_by_user;
    }

    public function vote(){
        if(!auth()->check()) return redirect('login');
        if($this->hasVoted) {
            try{
                $this->idea->removeVote(auth()->user());
            }catch (VoteNotFoundException $e){
                //No need to do anything
            }
            $this->hasVoted=false;
            $this->votesCount--;
        }
        else {
            try{
                $this->idea->vote(auth()->user());
            } catch (DuplicateVoteException $e){
                //No need to do anything
            }
            $this->hasVoted=true;
            $this->votesCount++;
        }
    }

    public function render()
    {
        return view('livewire.idea-index');
    }
}
