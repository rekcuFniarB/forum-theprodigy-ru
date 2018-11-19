<?php
namespace Prodigy\Respond;

class Comments extends Respond
{
    /**
     * Prepare comments data
     * @param string $comments   raw comments data
     * @param string $autonr     message author
     * @param bool   $cubscribed are comments subscribed?
     * @return array
     */
    public function prepare($comments, $author, $subscribed)
    {
        $csvdata = is_array($comments) ? $comments : explode("\r\n", $comments);
        $boardStr = (!isset($board) or empty($board) or $board == null or $board == "")? -1 : $board;
        
        $out = array();
        $out['comment_count'] = 0;
        $out['remaining_char_count'] = 0;
        $out['comments'] = array();
        $out['authors'] = array();
        
        $csvLength = count($csvdata) - 1;
        foreach ( $csvdata as $lineNr => $csvline )
        {
            if (empty($csvline)) continue;
        
            $out['comment_count']++;
            $out['remaining_char_count'] += strlen($csvline);
            
            $data = explode("#;#", $csvline);
            
            if(sizeof($data) < 4)
                continue;
            
            // Skip comments of ignored users
            if ($this->app->user->inIgnore($data[1]))
                continue;
            
            $ln = $lineNr + 1;
            
            $out['comments'][$ln] = array(
                'username' => $data[1],
                'realname' => $data[0],
                'date'     => $data[2],
                'comment'  => $data[3],
                'userinfo' => $this->app->user->loadDisplay($data[1]),
                'allow_modify' => ($this->app->user->name == $data[1] && $ln == $csvLength)
            );
            $out['authors'][] = $data[1];
        }
        
        $out['subscribed'] = ($subscribed or ($subscribed === null and ($author == $this->app->user->name or in_array($this->app->user->name, $out['authors']))));
        
        $out['remaining_char_count'] = 10000 - $out['remaining_char_count'];

        return $out;
    }
}
