<?php

// Usage:
//     $aq = new arrayQuery();
//     $aq($arr)->set('foo.bar.baz', 'qwerty');
//     echo $aq($arr)->get('foo.bar.baz');
//     $aq($arr)->del('foo.bar.baz');

namespace rekcuFniarB\arrayQuery;

class arrayQuery {
    protected $data;

    public function get($path, $default = null) {
        $arr = &$this->data;
        $path = explode('.', $path);
        foreach ($path as $key) {
            if (isset($arr[$key])) {
                $arr = &$arr[$key];
            }
            else
            {
                return $default;
            }
        }
        return $arr;
    } // get()

    public function set($path, $val = null) {
        $arr = &$this->data;
        $path = explode('.', $path);
        $last = $path[count($path) - 1];
        foreach ($path as $key) {
            if ($key != $last) {
                if (isset($arr[$key])) {
                    $arr = &$arr[$key];
                }
                else
                {
                    if (!is_array($arr))
                        $arr = array();
                    $arr[$key] = array();
                    $arr = &$arr[$key];
                }
            }
        }
        
        $prev = empty($arr[$last]) ? null : $arr[$last];
        if (!is_array($arr))
            $arr = array();
        $arr[$last] = $val;
        return $prev; // returning old value
    } // set()
    
    public function del($path) {
        $arr = &$this->data;
        $path = explode('.', $path);
        $last = $path[count($path) - 1];
        foreach ($path as $key) {
            if (isset($arr[$key])) {
                if ($key == $last) {
                    $prev = $arr[$key];
                    unset($arr[$key]);
                    return $prev; // returning old value
                }
                else
                    $arr = &$arr[$key];
            }
        }
    } // del()
    
    public function isset($path) {
        $arr = &$this->data;
        $path = explode('.', $path);
        foreach ($path as $key) {
            if (isset($arr[$key])) {
                $arr = &$arr[$key];
            }
            else
            {
                return false;
            }
        }
        return true;
    } // isset()
    
    public function getArray() {
        return $this->data;
    }
    
    public function __invoke(&$arr) {
        $this->data = &$arr;
        return $this;
    }
} // class arrayQuery
