<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Carbon\Carbon;

use App\Classes\SlackClass;

use App\Models\Mfl_temporary_url;
use App\Models\Mfl_slack_integration;

class RegisterFormController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }


    public function index(Request $request){
        //Check the timestamp of the query param
        $tokenParam = $request->query('token');

        //Check the DB for this param and get the timestamp
        $validUrl = Mfl_temporary_url::where(
                            'param', $tokenParam
                            )
                            ->where(
                                'created_at', '>', Carbon::now()->subHour()->toDateTimeString()
                            )
                            ->first();

        //If this returns nothing they expired, no form for them.
        if( is_null($validUrl) ){
            return "404";
        }
        
        //Get the slack team
        $slackTeam = $validUrl->slack_team;
        
        //Get the League ID
        $leagueID = SlackClass::getLeagueID($slackTeam);

        //Give them the form to fill out
        var_dump($request->query('token'));
        return view('mfl-slack-form')->with('leagueID', $leagueID);
    }
    
    public function verify(Request $request){
        var_dump($request->input());
        return "hazaaarr";
    }
    /**
    * Use the form to get MFL user cookie
    *
    * @param Request $request
    * @return json
    */
    public static function registerUser(Request $request){
        //Get form field info, attempt to sign-in, get cookie
            //We get the cookie and slack username from this
        //Store mfl_registered_users
            //Store teh cookie we got, don't need email & pw yet'
        //Store reg user meta (user_id, league_id, slack username)
            //User ID from updateorCreate above, league_id from team id from slack command
            // slack username from form
        //slack/email me, return view of success
        $leagueID = $request->input('league');
        $mflUsername = $request->input('username');
        $mflPassword = $request->input('password');

        $mflLoginBaseUrl = "https://api.myfantasyleague.com/2017/login?";
        $mflLoginParams = "L={$leagueID}&USERNAME={$mflUsername}&PASSWORD={$mflPassword}&JSON=1";
        
        $mflLoginCookies = SlackClass::getMflData($mflLoginBaseUrl . $mflLoginParams);
        var_dump($mflLoginCookies);
//   'username-1' => string 'asdasd' (length=6)
//   'username-2' => null
//   'username-3' => null
    }
}
