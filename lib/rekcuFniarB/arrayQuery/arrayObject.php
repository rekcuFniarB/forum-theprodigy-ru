<?php

// Same as queriedArray but stores data indide self
// instead of referencing to outer array.
// 
// Usage:
//     $aq = new arrayObject($arr);
//     $aq->set('foo.bar.baz', 'qwerty');
//     echo $aq->get('foo.bar.baz');
//     $dq->del('foo.bar.baz');

namespace rekcuFniarB\arrayQuery;

class arrayObject extends arrayQuery {
    protected $data;
    
    public function __construct($arr) {
        $this->data = $arr;
    }
    
//     public function __get($key) {
//         if (isset($this->data[$key]))
//             if (is_array($this[$key]))
//                 return new arrayObject($this[$key]);
//             else
//                 return $this->data[$key];
//         else
//             return null;
//     }
} // class queriedArray
