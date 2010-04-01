<?php

require_once "JSON.php";


/*
Author : Arun Gnanamani (g.arun@yahoo.com)

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.

*/

/*
This ia a small library which I wrote to play around with friendfeed apis. It uses friendfeed's apis to provide an even better abstraction for some of the common 
things like posting to ff account,getting information about friends, extracting posts and all the urls referenced. This is similar to twippi, which is the same 
I wrote for creating a thin client for twitter.
*/




/*
Publish to ff's stream
*/
function post_to_friend_feed(&$nickname,&$remotekey,&$msg,&$link){
	$friendfeed = new FriendFeed($nickname,$remotekey);
	if($link){
		return $friendfeed->publish_link($msg, $link);
	}
	else{
                return $friendfeed->publish_message($msg);
	}
}


/*
Get user profile
*/
function get_friend_feed_profile(&$nickname){
        $url = 'http://friendfeed.com/api/user/' . $nickname . '/profile';

        $curl_handle = curl_init();
        curl_setopt($curl_handle, CURLOPT_URL, "$url");
        curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
        $buffer = curl_exec($curl_handle);
        curl_close($curl_handle);
        // check for success or failure
        if (!empty($buffer)) {
            return $buffer;
        } else {
            echo 'friendfeed's profile not ok';
        }

}


/*
Get details of an user's profile, including information about thier subscribers(friends)
*/
function get_friend_feed_friends_info(&$nickname){
	$ret=get_friend_feed_profile($nickname);
	$json = new Services_JSON();
	$obj=$json->decode($ret); //huge copying if the user has many friends.
        echo "Name: " . $obj->{'name'} . "<br>";
        echo "NickName: " . $obj->{'nickname'}  . "<br>";
        
	echo "<br><br>User's subscriptions:<br>";

        //services the user is subscribed to
        foreach ($obj->{'services'} as $val){
           echo "UserName: " . $val->{'username'}  . "<br>";
           echo "ProfileUrl: " . $val->{'profileUrl'}  . "<br>";
           echo "Service: " . $val->{'name'}  . "<br>";
           echo "---------------------<br>";
        }
	
	echo "<br><br>Subscriptions/Network<br><br>";	
	//subsciptions(friends) details
	foreach ($obj->{'subscriptions'} as $val){
	   echo "Friend Name: " . $val->{'name'}  . "<br>";
	   echo "Friend NickName: " . $val->{'nickname'}  . "<br>";
	   echo "---------------------<br>";
	}

	echo "<br><br>";

}


/*
 Get the publuc feeds from an user's ff stream. if there are links within messages show them seperately
*/
function get_friend_feed_user_public_feeds(&$uname,&$nickname,$num_messages){
        $url = 'http://friendfeed.com/api/feed/user/' . $nickname ;

        $curl_handle = curl_init();
        curl_setopt($curl_handle, CURLOPT_URL, "$url");
        curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
        $ret = curl_exec($curl_handle);
        curl_close($curl_handle);

	if(!$ret){
		echo "Couldn't get feeds";
		return;
	}
	else{
		;//echo "Got feeds";
	}

        $json = new Services_JSON();
        $obj=$json->decode($ret); //huge copying if the user has many friends.
        echo "<br><br>";

        //services the user is subscribed to
        echo "These messages with url have automatically been added<br>";
        foreach ($obj->{'entries'} as $val){
                 $serv= $val->{'service'};
                 $entry=$serv->{'entryType'};
                 switch($entry){
                  case "message" : 
			if(vlib_msg_has_url($val->{'title'})){
		                echo "Added Comment: " . $val->{'title'} . "<br>";
                		echo "Url: " . $val->{'link'} . "<br>";
				postUserComment($uname,$val->{'title'},$val->{'link'},$x=0,$ip="1.2.3.4");
				echo "---------------<br>";
			}
			break;
                  case "link" : 
                                echo $val->{'title'}."<br>";
                                echo $val->{'link'}."<br>";
		                echo "---------------<br>";
				break;
                 }
	}
}

?>