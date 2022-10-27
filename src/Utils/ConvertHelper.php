<?php

namespace App\Utils;

use DOMDocument;

class ConvertHelper
{
    static function getdata($contents)
    {
        $DOM = new DOMDocument;
        $DOM->loadHTML($contents);
        $items = $DOM->getElementsByTagName('tr');
        $return = array();
        foreach ($items as $node) {
            $return[] = self::tdrows($node->childNodes);
        }
        return $return;
    }

    static function tdrows($elements)
    {
        $str = array();
        foreach ($elements as $element) {
            $str[] = $element->nodeValue;
        }

        return $str;
      //  return $str[0];
    }
    static function getdataLien($contents)
    {
        $DOM = new DOMDocument;
        $DOM->loadHTML($contents);
        $items = $DOM->getElementsByTagName('li');
        $return = array();
        foreach ($items as $node) {
            $return[] = $node->nodeValue;
        }
        return $return;
    }
}