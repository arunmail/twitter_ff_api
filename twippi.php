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
A library which I wrote to play around with twitter apis. It uses twitter's apis to provide an even better abstraction for some of the common things like posting 
to twitter account,getting information about friends, extracting posts and all the urls referenced.
*/



/*
	Given username/password for an user, post this message to the user's account
*/
function post_msg_twitter(&$username,&$password,&$message){
        // The twitter API address
        $url = 'http://twitter.com/statuses/update.xml';
        // Alternative JSON version
        // $url = 'http://twitter.com/statuses/update.json';
        // Set up and execute the curl process
        $curl_handle = curl_init();
        curl_setopt($curl_handle, CURLOPT_URL, "$url");
        curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl_handle, CURLOPT_POST, 1);
        curl_setopt($curl_handle, CURLOPT_POSTFIELDS, "status=$message");
        curl_setopt($curl_handle, CURLOPT_USERPWD, "$username:$password");
        $buffer = curl_exec($curl_handle);
        curl_close($curl_handle);
        // check for success or failure
        if (!empty($buffer)) {
            print "Twittered!\n";
            return 0;
        } else {
            print "Oops not able to post on twitter!\n";
            return -1;
        }
}


/*
	Get a list of followers
*/
function get_twitter_friends(&$username,&$password){
        $url = 'http://twitter.com/statuses/friends.json'; //works only for authenticated user
        $curl_handle = curl_init();
        curl_setopt($curl_handle, CURLOPT_URL, "$url");
        curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl_handle, CURLOPT_USERPWD, "$username:$password");

        $buffer = curl_exec($curl_handle);
        curl_close($curl_handle);
        // check for success or failure
        if (!empty($buffer)) {
            return $buffer;
        } else {
            print "cant get twitter friends\n";
	    return -1;
        }

}

/*
	Get recent (num_messages) messages
*/

function get_twitter_user_messages(&$nickname,$num_messages){
        $url = 'http://twitter.com/statuses/user_timeline/'. $nickname .'.json?count='.$num_messages;
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
            print "cant get messages\n";
	    return -1;
        }

}

/*
	Print the list of friends and thier profile details in a readable format
*/

function & get_twitter_friends_info(&$username,&$password){
	$ret=get_twitter_friends($username,$password);
	$json = new Services_JSON();
	$obj=$json->decode($ret); //huge copying if the user has many friends.

	//subsciptions(friends) details
	foreach ($obj as $val){
           print "UserName: " . $val->{'screen_name'} . "\n";
	   print "Name: " . $val->{'name'} . "\n";
	   print "Location: " . $val->{'location'}  . "\n";
	   print "Url: " . $val->{'url'}  . "\n";
	   print "Description: " . $val->{'description'}  . "\n";
	   print "Followers count: " . $val->{'followers_count'}  . "\n";
	   print "---------------------\n\n";
	}

	return 0;
}

/*
	Print the messages in a readable form
*/

function get_twitter_message_info(&$nickname,$num_messages){
	$ret=get_twitter_user_messages($nickname,$num_messages);
       	$json = new Services_JSON();
	$obj=$json->decode($ret); 

	foreach ($obj as $val){
		$msg=$val->{'text'};
		// the regex below was used from http://snipplr.com/view/2371/regex-regular-expression-to-match-a-url/
		$url_regex="/https?:\/\/([-\w\.]+)+(:\d+)?(\/([\w\/_\.]*(\?\S+)?)?)?/";
	        if(preg_match($url_regex,$msg)){
			$non_url_part=preg_replace($url_regex,"",$msg);
			preg_match($url_regex,$msg,$url);
	                print "Url: " . $url[0]."\n" ;
		}
		print "Message: " . $msg ."\n"; 
   		print "Time: " . $val->{'created_at'} ."\n" ;
	        print "---------------------\n\n";
	}
}



/*
A small function to test the above functions. Try to play around
*/
function twitter_tester(){
	get_twitter_message_info($nickname="paul",$num_messages=10);
}

//NOTE: uncomment this to test
//twitter_tester();

?>