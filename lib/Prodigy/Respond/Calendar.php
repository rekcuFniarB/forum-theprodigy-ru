<?php
namespace Prodigy\Respond;

class Calendar extends Respond
{
    public function getCurrentTime()
    {
        $timeUserOff = (isset($this->app->user->timeOffset) ? $this->app->user->timeOffset : 0);
        return time() + (($this->app->conf->timeoffset + $timeUserOff) * 3600);
    }
    
    public function createHolidayArray($monthp1, $year, $day = NULL)
    {
        // Holidays. Note that with the holidays the month in the table is NOT zero based like it is with
        // events. I did this because this table is more likely to get interaction directly from an admin
        // and having to remember to subtract one from the months is a pain.
        $db_prefix = $this->app->db->prefix;
        $holidays = array();
        $strSql = "SELECT day,year,title FROM {$db_prefix}calendar_holiday WHERE month=? AND ((year IS NULL) or year=?)";
        $qparams = array($monthp1, $year);
        
        if ($day != NULL)
        {
            $strSql .= " AND day=?";
            $qparams[] = $day;
        }
        
        $dbst = $this->app->db->prepare($strSql);
        $dbst->execute($qparams);
        
        while ($row = $dbst->fetch())
        {
            // The convention in the table is for holidays that always happen on the same date to have NULL for
            // the year value. If a row in the holiday table does have a year value then only print that holiday
            // for the specific year. Holidays that float can be added ahead of time with the year values given 
            // to make them show up on the right days.
            if ($row['year'] == NULL || $row['year'] == $year)
            {
                // I'm setting the colors on holidays as they print rather than in the string here. The reason
                // being I have holidays all one color and if multiple are on the same day it becomes akward
                // to try and set the <font> here -- or redundant if it happens on each one for each day.
                if (!isset($holidays[$row['day']]))
                    $holidays[$row['day']] = "{$this->app->locale->calendar5} {$row['title']}";
                else
                    $holidays[$row['day']] .= ", {$row['title']}";
            }
        }
        return($holidays);
    }
    
    // $day has been added as an optional parameter to restrict the results to a particular day.
    // This is used by the board index to only find members of the current day.
    function createBirthdayArray($monthp1,$year,$day=NULL)
    {
        $db_prefix = $this->app->db->prefix;
        // Collect all of the birthdays for this month and precreate the strings to use for display.
        $bday = array();
        $strSql = "SELECT dayofmonth(birthdate) as dom, membername, realname, year(birthdate) as year FROM {$db_prefix}members WHERE month(birthdate) = ?";
        $qparams = array($monthp1);
        
        if ($day != NULL) {
            $strSql .= " AND dayofmonth(birthdate) = ?";
            $qparams[] = $day;
        }
        
        $dbst = $this->app->db->prepare($strSql);
        while ($row = $dbst->fetch())
        {
            $euser=urlencode($row['membername']);
            if ($row['year'] > 0 && $row['year'] <= $year)
            {
                $ageNum = $year - $row['year'];
                $age = " ($ageNum)";
            }
            else
                $age = '';
            if (!isset($bday[$row['dom']]))
                $bday[$row['dom']] = '<font class="calendar">' . $this->app->locale->calendar3 . '</font> <a href="' . SITE_ROOT . '/people/' . $euser . '/">' . $this->service->esc($row['realname']) . $age . '</a>';
            else
                $bday[$row['dom']] .= ', <a href="' . SITE_ROOT . '/people/' . $euser . '/">' . $this->service->esc($row['realname']) . '' . $age . '</a>';
        }
        return($bday);
    }

    function createEventArray($bPowerUser, $cats, $month, $year, $day = NULL)
    {
        $db_prefix = $this->app->db->prefix;
        
        $events = array();
        $strSql = "SELECT cal.day,cal.title,cal.id_board,b.ID_CAT,cal.id_topic,cal.id_member,cal.id ";
        $strSql .= "FROM {$db_prefix}calendar as cal, {$db_prefix}boards as b ";
        $strSql .= "WHERE cal.month=? AND cal.year=? AND cal.id_board = b.ID_BOARD";
        
        $qparams = array($month, $year);
        
        if ($day != NULL)
        {
            $strSql .= " AND cal.day = ?";
            $qparams[] = $day;
        }
        $dbst = $this->app->db->prepare($strSql);
        $dbst->execute($qparams);
        while ($row = $dbst->fetch())
        {
            if ($bPowerUser || $row['id_member'] == $this->app->user->id)
                $strOwner = '<a href="' . SITE_ROOT . '/calendar/ee/' . $row[id] . '/"><font color="#FF0000">*</font></a>';
            else
                $strOwner = '';
            
            // See if the user has access to the board this event was posted in.
            if ($cats[$row['ID_CAT']][0] == '' || $bPowerUser || in_array($this->app->user->group, $cats[$row['ID_CAT']]))
            {
                if (!isset($events[$row['day']]))
                    $events[$row['day']] = '<font color="#' . $this->app->conf->cal_eventcolor . '">' . $this->locale->calendar4 . '</font> ' . $strOwner . '<a href="' . SITE_ROOT . '/b' . $row['id_board'] . '/t' . $row['id_topic'] . '/">' . $this->service->esc($row['title']) . '</a>';
                else
                    $events[$row['day']] .= ', ' . $strOwner . '<a href="' . SITE_ROOT . '/b' . $row['id_board'] . '/t' . $row['id_topic'] . '/">' . $this->service->esc($row['title']) . '</a>';
            }
        }
        $dbst = null;
        return($events);
    }
    
    // Called from BoardIndex.php to display the current day's events on the board index.
    public function getEvents()
    {
        //global $modSettings, $db, $db_prefix, $txt, $imagesdir, $settings, $scripturl;
        if (!$this->app->conf->cal_enabled)
            return null;

        // Make sure at least one of the options is checked.
        if (!$this->app->conf->cal_showeventsonindex && !$this->app->conf->cal_showbdaysonindex && !$this->app->conf->cal_showholidaysonindex)
            return null;
        
        $today = localtime($this->getCurrentTime());
        $month = $today[4];
        $year = $today[5] + 1900;
        $day = $today[3];
        
        $calendar = array('day' => $day);
        
        $bechoedHeader = false;
        
        if ($this->app->conf->cal_showholidaysonindex)
        {
            $calendar['holidays'] = $this->createHolidayArray($month + 1, $year, $day);
        }

        if ($this->app->conf->cal_showbdaysonindex)
        {
            $calendar['bday'] = $this->createBirthdayArray($month + 1, $year, $day);
        }

        if ($this->app->conf->cal_showeventsonindex)
        {
            $bPowerUser = ($this->app->user->accessLevel() > 2);
            $cats = array();
            $db_prefix = $this->app->db->prefix;
            $rs = $this->app->db->query("SELECT ID_CAT,membergroups FROM {$db_prefix}categories");
            while ($row = $rs->fetch())
                $cats[$row['ID_CAT']] = explode(',', $row['membergroups']);
            
            $calendar['events'] = $this->createEventArray($bPowerUser, $cats, $month, $year, $day);
        }
        return $calendar;
    } // getEvents()
    
    public function ValidatePost()
    {
        return true; // FIXME
    }
    
    public function canPost() {
        return true; // FIXME
    }
}
