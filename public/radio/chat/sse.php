<?php
include_once("../../Settings.php");
include_once("../../Config.php");
include_once("../../$sourcedir/theprodigy.ru/MySQLDatabase.class.php");
include_once("../../$sourcedir/Subs.php");
include_once("../../$sourcedir/Errors.php");

// $mysqli = mysqli_connect($db_server, $db_user, $db_passwd, $db_name);
// mysqli_set_charset ($mysqli , "utf8");

$db = new MySQLDatabase($db_server, $db_user, $db_passwd, $db_name, $db_prefix, 'UTF8');
$db->connect();

/**
 * Preparing new message to client using SSE protocol (Server Side Events aka EventSource).
 * @param $timestamp ID to remember. Client will request it when reconnecting.
 * @param $msg New message data to deliver.
 */
function newMessage($timestamp, $msg) {
    $output = '';
    $output .= "id: $timestamp\n";
    $output .= "data: $msg\n";
    $output .= "\n";
    return $output;
}

function sendChunk($chunk=null, $pad=false) {
    if($chunk !== null) {
        // Apache + PHP-FPM buffer workaround
        if ($pad) {
            $padlength = 4096 - strlen($chunk);
            if ($padlength > 0)
                $chunk = str_pad($chunk, $padlength, "\0");
        }
    }    
    
    // if chunk is null, will finish response.
    printf("%x\r\n", strlen($chunk));
    echo "$chunk\r\n";
    
    while (@ob_get_level() > 0) {
        @ob_end_flush();
    }
    
    @ob_flush();
    @flush();
}

header('Content-Type: text/event-stream; charset=utf-8');
header('Cache-Control: no-cache');
header('Transfer-Encoding: chunked');

$lastTimestamp = empty($_SERVER["HTTP_LAST_EVENT_ID"]) ? false : $_SERVER["HTTP_LAST_EVENT_ID"];
        
$room = empty($_GET['room']) ? false : $_GET['room'];
$dopad = !empty($_GET['pad']);

sendChunk("\nretry: 5000\n\n", $dopad);

if (!$room)
    sendChunk(newMessage(time(), "__NO_ROOM__"));
else {
    $messages = '';
    while (true) {
        if ($lastTimestamp){
            // Show new messages after last check
            $request = $db->query("SELECT m.memberName, m.realName, c.body, UNIX_TIMESTAMP(c.date) as date FROM {$db->db_prefix}chat AS c LEFT JOIN {$db->db_prefix}members AS m ON c.ID_MEMBER = m.ID_MEMBER
                WHERE date > FROM_UNIXTIME(" . $db->escape_string($lastTimestamp) . ") AND room = '" . $db->escape_string($room) . "' ORDER BY date")
                or sendChunk(newMessage(time(), "ERROR:\n" . __FILE__ . "\n" . __LINE__));
        }
        else {
            // Or show last 25 messages
            $request = $db->query("(SELECT m.memberName, m.realName, c.body, UNIX_TIMESTAMP(c.date) as date FROM {$db->db_prefix}chat AS c LEFT JOIN {$db->db_prefix}members AS m ON c.ID_MEMBER = m.ID_MEMBER
                WHERE room = '" . $db->escape_string($room) . "' ORDER BY date DESC LIMIT 10) ORDER BY date ASC")
                or sendChunk(newMessage(time(), "ERROR:\n" . $db->error . __FILE__ . "\n" . __LINE__));
        }
        
        if ($request->num_rows > 0){
            while ($msg = $request->fetch_assoc()){
                $msg['body'] = doUBBC($msg['body']);
                $lastTimestamp = $msg['date'];
                //$messages .= newMessage($lastTimestamp, json_encode($msg));
                // This way works faster
                sendChunk(newMessage($lastTimestamp, json_encode($msg)), $dopad);
            }
        }
        
        //if (!empty($messages)) {
            //sendChunk($messages, $dopad);
            //$messages = '';
        //}
        
        usleep(3000000);
    }
}

sendChunk(null);
