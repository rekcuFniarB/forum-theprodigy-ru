<?php
// TODO Broken, this code should be rewritten

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
 * Delivering new message to client using SSE protocol (Server Side Events aka EventSource).
 * @param $timestamp ID to remember. Client will request it when reconnecting.
 * @param $msg New message data to deliver.
 */
function returnMessage($timestamp, $msg) {
    echo "id: $timestamp" . PHP_EOL;
    echo "data: $msg" . PHP_EOL;
    echo PHP_EOL;
}

header('Content-Type: text/event-stream; charset=utf-8');
header('Cache-Control: no-cache');

echo PHP_EOL . PHP_EOL;
echo "retry: 5000" . PHP_EOL . PHP_EOL;

$lastTimestamp = empty($_SERVER["HTTP_LAST_EVENT_ID"]) ? false : $_SERVER["HTTP_LAST_EVENT_ID"];
        
$room = empty($_GET['room']) ? false : $_GET['room'];
    
if (!$room)
    returnMessage(time(), "__NO_ROOM__");
else {
    while (true) {
        if ($lastTimestamp){
            // Show new messages after last check
            $request = $db->query("SELECT m.memberName, m.realName, c.body, UNIX_TIMESTAMP(c.date) as date FROM {$db->db_prefix}chat AS c LEFT JOIN {$db->db_prefix}members AS m ON c.ID_MEMBER = m.ID_MEMBER
                WHERE date > FROM_UNIXTIME(" . $db->escape_string($lastTimestamp) . ") AND room = '" . $db->escape_string($room) . "' ORDER BY date")
                or returnMessage(time(), "ERROR:\n" . __FILE__ . "\n" . __LINE__);
        }
        else {
            // Or show last 25 messages
            $request = $db->query("(SELECT m.memberName, m.realName, c.body, UNIX_TIMESTAMP(c.date) as date FROM {$db->db_prefix}chat AS c LEFT JOIN {$db->db_prefix}members AS m ON c.ID_MEMBER = m.ID_MEMBER
                WHERE room = '" . $db->escape_string($room) . "' ORDER BY date DESC LIMIT 10) ORDER BY date ASC")
                or returnMessage(time(), "ERROR:\n" . $db->error . __FILE__ . "\n" . __LINE__);
        }
        
        if ($request->num_rows > 0){
            while ($msg = $request->fetch_assoc()){
//                 $msg['body'] = mb_convert_encoding($msg['body'], 'UTF-8', 'CP1251');
//                 $msg['memberName'] = mb_convert_encoding($msg['memberName'], 'UTF-8', 'CP1251');
//                 $msg['realName'] = mb_convert_encoding($msg['realName'], 'UTF-8', 'CP1251');
                $msg['body'] = doUBBC($msg['body']);
                $lastTimestamp = $msg['date'];
                returnMessage($lastTimestamp, json_encode($msg));
            }
        }

//         ob_end_flush();
     ob_flush();
        flush();
        usleep(3000000);
    }
}
