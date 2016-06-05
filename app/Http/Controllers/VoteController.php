<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use App\Http\Requests;
use App\Vote;
use App\User;

class VoteController extends Controller
{
    /**
     * Store a newly created resource in storage.
     * Example http://musicpoll/api/v1/vote/?genre=1&alias=kot+begemot&api_token=1234567890qwertyuiop
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->all();
        $data['user_id'] = \Auth::guard('api')->user()->id;

        if($this->validator($data)->fails()) {
            return response()->json(['status'=>'error','message'=>$this->validator($data)->messages()],400);
        }

        $vote = Vote::where('user_id', $data['user_id'])->get();

        if (!$vote->isEmpty()){
            return response()->json(['status'=>'error','message'=>'You already voted.'],400);
        }

        try {
            $this->createVote($data);
        } catch (\Exception $e) {
            \Log::error( $e->getMessage() );
            return response()->json(['status'=>'error','message'=>$e->getMessage()],400);
        }

        $rates = Vote::getCurrentRate();
        $message = htmlentities(\Auth::guard('api')->user()->alias) .
            ',' . $rates[$data['genre']]['percentage'] .
            "% also like " . $rates[$data['genre']]['description'].
            "!";

        return response()->json(['status'=>'success','message'=>$message,'rates'=>$rates],200);
    }

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'alias' => 'required|max:255',
            'genre' => 'required|integer|exists:genres,id'
        ]);
    }

    protected function createVote(array $data)
    {
        User::where('id',$data['user_id'])->update(['alias' => $data['alias']]);

        return Vote::create([
            'genre_id' => $data['genre'],
            'user_id' => $data['user_id']
        ]);
    }

    /**
     * Display the the results if already voted.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $vote = Vote::where('user_id', \Auth::guard('api')->user()->id)->get();

        if ($vote->isEmpty()){
            return response()->json(['status'=>'error','message'=>'You did not vote.'],400);
        }

        $votedGenreId = \Auth::guard('api')->user()->vote()->first()->genre_id;

        $rates = Vote::getCurrentRate();
        $message = htmlentities(\Auth::guard('api')->user()->alias) .
            ',' . $rates[$votedGenreId]['percentage'] .
            "% also like " . $rates[$votedGenreId]['description'].
            "!";

        return response()->json(['status'=>'success','message'=>$message,'rates'=>$rates],200);
    }
}
