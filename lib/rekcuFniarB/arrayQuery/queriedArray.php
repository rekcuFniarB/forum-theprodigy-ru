<?php

// Usage:
//     $aq = new queriedArray($arr);
//     $aq->set('foo.bar.baz', 'qwerty');
//     echo $aq->get('foo.bar.baz');
//     $dq->del('foo.bar.baz');

namespace rekcuFniarB\arrayQuery;

class queriedArray extends arrayQuery {
    protected $data;
    
    public function __construct(&$arr) {
        $this->data = &$arr;
    }
} // class queriedArray
