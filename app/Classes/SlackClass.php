<?php

namespace App\Classes;

use App\Models\Mfl_franchise_map;
use App\Models\Mfl_players_table;
use App\Models\Mfl_slack_integration;

class SlackClass{

    protected $slackWebhook;
    protected $slackMessage;

    /**
    * Get the slack message
    *
    * @return string
    */
    public function getSlackMessage(){
        return $this->slackMessage;
    }

    /**
    * Set the slack slackMessage 
    *
    * @param string $message;
    * @return $this
    */
    public function setSlackMessage($slackMessage){
        $this->slackMessage = $slackMessage;
        return $this;
    }

    /**
    * Get the league ID from the integration table
    *
    * @param string $slackTeamID
    * @return string $MflLeagueID
    */
    public static function getLeagueID($slackTeamID){
        return Mfl_slack_integration::find($slackTeamID)->mfl_league_id;
    }

    /**
    * Create the URL for retrieving the data 
    *
    * @param string $dataType [Param for what kind of data to retrieve]
    * @param string $leagueID
    * @param string $week 
    * @param string $additionalArgs
    * @return string
    */
    public static function getMflLeagueDataUrl($dataType, $leagueID = '', $week = '', $additionalArgs = ''){
       
        $mflBaseUrl = 'https://www74.myfantasyleague.com/2017/export?';

        //Type of data to export
        $typeUrlArg = "TYPE={$dataType}";
        $leagueUrlArg = "&L={$leagueID}";
        $weekUrlArg = "&W={$week}";
        $jsonUrlArg = "&JSON=1";

        //Build the full URL
        return $mflBaseUrl . $typeUrlArg . $leagueUrlArg . $weekUrlArg . $additionalArgs . $jsonUrlArg;

    }

    /* DEPRECATED?
    * Generate the URL for getting information on players
    */
    public function getMflPlayerDataUrl($players){
        //Type of data to export
        $typeUrlArg = "TYPE=players";
        $playersUrlArg = "&PLAYERS={$players}";
        $jsonUrlArg = "&JSON=1";

        return MFL_BASE_URL . $typeUrlArg . $playersUrlArg . $jsonUrlArg;
    }

    /**
    * Function to return the JSON object of the data requested
    *
    * @param string $dataUrl 
    * @param string $cookie
    * @return string
    */
    public static function getMflData($dataUrl, $cookie = ''){
var_dump($dataUrl);

        //Check if a cookie is being passed through
        if($cookie !== ''){
            //Do more work cus we got cookies nom nom
            var_dump("adsasdasd");
            $opts = [
                "http" => [
                    "header" => "Cookie: MFL_USER_ID={$cookie}"
                ]
            ];
            $context = stream_context_create($opts);
            $mflData = @file_get_contents($dataUrl, false, $context);
        }else{
            //Get the contents of the url
            $mflData = @file_get_contents($dataUrl);
        }

        //Check to make sure there is data
        if(!$mflData){
            exit("Failed to retrieve data from MFL");
        }
        
        //Make sure we can decode the json
        $mflDataObj = json_decode($mflData);
        if(!$mflData){
            exit("Failed to decode the data from MFL");
        }

        return $mflDataObj;
    }

    /**
    * Retrieve the players from the DB
    *
    * @param array $playerIDs 
    * @return Eloquent Object
    */
    public static function queryPlayers($playerIDs){

        return Mfl_players_table::whereIn('id', $playerIDs)
                                        ->orderBy('name', 'asc')
                                        ->get();
    }

    /**
    * Convert slack easy franchise ID to MFL format
    *
    * @param
    * @return string 
    */
    public static function formatFranchiseID($franchiseID){
        //Convert franchiseID to correct format 000#
        switch (strlen($franchiseID)) {
            case 1:
                return $franchiseID = "000{$franchiseID}";
            case 2:
                return $franchiseID = "00{$franchiseID}";
        }
    }

    /**
    * Separate out the picks in the string and return
    *
    * @param string $combinedString
    * @return Array 
    */
    public static function separatePlayersPicks($combinedString){
        $splitArray = new \stdClass();
        $playerIds = [];
        $draftPickIds = [];

        $playersPicksArray = explode(",", $combinedString);
        //Loop through and assign to proper array
        foreach($playersPicksArray as $key => $id){
            
            if(strpos($id, 'DP_') === 0 || strpos($id, 'FP_') === 0){
                array_push($draftPickIds, $id);
            }elseif($id !== ""){
                array_push($playerIds, $id);
            }
        }

        $splitArray->draftPicks = $draftPickIds;
        $splitArray->players = $playerIds;
        return $splitArray;
    }


    /**
    * Retrieve the name of a franchise from the DB
    *
    * @param string $franchiseID
    * @return string 
    */
    public static function getFranchiseName($franchiseID){

        return Mfl_franchise_map::find($franchiseID)
            ->franchise_name;
    }


    /**
    * Take the player ID's and find them in the DB
    *
    * @param array $playerIDs
    */
    public static function getPrettyPlayers($playerIDs){

        //Retrieve the players from the DB
        $players = self::queryPlayers($playerIDs);
        //Array for adding players
        $playersArr = [];

        //Make sure to only process if results where returned
        if(count($players)){
            foreach($players as $player){
                
                //Put this in a string format to display in slack
                $playerInfoString = "    *{$player->name}*"
                                    . "    _{$player->team}_  "
                                    . "    _{$player->position}_";
                array_push($playersArr, $playerInfoString);
            }
        }

        return $playersArr;
    }

    /**
    * Take the player ID's and find them in the DB
    *
    * @param array $playerIDs
    */
    public static function getPrettyRoster($playerIDs){

        //Retrieve the players from the DB
        $players = self::queryPlayers($playerIDs);
        
        //Arrays for positions
        $qbArr = [];
        $rbArr = [];
        $wrArr = [];
        $teArr = [];

        //Make sure to only process if results where returned
        if(count($players)){
            foreach($players as $player){
                
                //Put this in a string format to display in slack
                $playerInfoString = "      *{$player->name}*"
                                    . "    _{$player->team}_  "
                                    . "    _{$player->position}_";

                switch ($player->position){
                    case 'QB':
                        array_push($qbArr, $playerInfoString);
                        break;
                    case 'RB':
                        array_push($rbArr, $playerInfoString);
                        break;
                    case 'WR':
                        array_push($wrArr, $playerInfoString);
                        break;
                    case 'TE':
                        array_push($teArr, $playerInfoString);
                        break;
                } 
            }
            $qbCount = count($qbArr);
            array_unshift($qbArr, "*QB's* ({$qbCount})");

            $rbCount = count($rbArr);
            array_unshift($rbArr, "*RB's* ({$rbCount})");

            $wrCount = count($wrArr);
            array_unshift($wrArr, "*WR's* ({$wrCount})");

            $teCount = count($teArr);
            array_unshift($teArr, "*TE's* ({$teCount})");
        }

        return array_merge($qbArr, $rbArr, $wrArr, $teArr);
    }

    /**
    * Gets trade pick ID's from offering and convert them to human readable
    *
    * @param array $draftPickIds
    * @return array
    */
    public static function getPrettyDraftPicks($draftPickIDs, $leagueID){
      
        //Array for adding players
        $draftPicksArr = [];

        foreach($draftPickIDs as $draftPick){
            //Get the parts from the draft pick
            $draftPickParts = explode("_", $draftPick);

            //This is a pick in the current year
            if($draftPickParts[0] === "DP"){
                //Add 1 to get the round and pick num
                $round = (int)$draftPickParts[1] + 1;
                $pickNum = (int)$draftPickParts[2] + 1;
                $pickNum = ($pickNum < 10? "0{$pickNum}" : $pickNum);

                $draftPickString = "    {$round}.$pickNum"; //Spacing for formatting
            }else{
                $team = SlackClass::getFranchiseName($leagueID ."_" . $draftPickParts[1]);
                $year = $draftPickParts[2];
                $round = $draftPickParts[3];

                switch($round % 10){
                    case 1: $round .= 'st'; break;
                    case 2: $round .= 'nd'; break;
                    case 3: $round .= 'rd'; break;
                    case 4: $round .= 'th'; break;
                }

                $draftPickString = "    {$year} {$round} {$team}"; //Spacing for formatting
            }

            //$draftPickString = SlackClass::decodeDraftPick($draftPick);
            array_push($draftPicksArr, $draftPickString);
        }

        return $draftPicksArr;
    }

    /**
    * Decode the draft pick
    *
    * @param string $draftPick
    * @return string
    */
    public static function decodeDraftPick($draftPick, $leagueID){

    }


    /**
    * Function to post a message to slack
    * 
    * @param string $message
    * @param string $slackWebhook
    */
    public static function sendSlackMsg($slackMessage, $slackWebhook)
    {
    // Make your message
    $data = array('payload' => json_encode(array('text' => $slackMessage)));

    // Use curl to send your message
    $c = curl_init($slackWebhook);
    curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($c, CURLOPT_POST, true);
    curl_setopt($c, CURLOPT_POSTFIELDS, $data);
    curl_exec($c);
    curl_close($c);
    }


}


?>