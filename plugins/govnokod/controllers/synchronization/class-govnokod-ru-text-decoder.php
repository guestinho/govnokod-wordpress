<?php

class GovnokodRuTextDecoder {
    public function decode($node) {
        $result = '';
        $this->_parseRecursive($node, $result);
        return trim($result);
    }

    // http://govnokod.ru/page/bbcode
    private static $_bbStyleMap = array(
        'color:blue;'                   => array('[color=blue]', '[/color]'),
        'color:green;'                  => array('[color=green]', '[/color]'),
        'color:red;'                    => array('[color=red]', '[/color]'),
        'color:white;'                  => array('[color=white]', '[/color]'),
        'text-decoration:underline;'    => array('[u]', '[/u]'),
        'text-decoration:blink;'        => array('[blink]', '[/blink]'),
        'text-decoration:line-through;' => array('[s]', '[/s]'),
        'font-size:20px;'               => array('[size=20]', '[/size]'),
        'font-size:15px;'               => array('[size=15]', '[/size]'),
        'font-size:10px;'               => array('[size=10]', '[/size]'),
    );

    private function _parseRecursive($node, &$result) {
        if ($node->nodetype !== HDOM_TYPE_ELEMENT) {
            $result .= htmlspecialchars_decode($node->innertext);
            return;
        }
        $open = '';
        $close = '';
        if ($node->tag === 'b') {
            $open = '[b]';
            $close = '[/b]';
        } else if ($node->tag === 'i') {
            $open = '[i]';
            $close = '[/i]';
        } else if ($node->tag === 'span' && isset(self::$_bbStyleMap[$node->style])) {
            list($open, $close) = self::$_bbStyleMap[$node->style];
        } else if ($node->tag === 'code') {
            $open = empty($node->class) ? '[code]' : '[code=' . $node->class . ']';
            $close = '[/code]';
        }

        $result .= $open;
        foreach ($node->nodes as $sub) {
            $this->_parseRecursive($sub, $result);
        }
        $result .= $close;
    }
}