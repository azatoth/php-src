<?php
/*
  +----------------------------------------------------------------------+
  | PHP Version 4                                                        |
  +----------------------------------------------------------------------+
  | Copyright (c) 1997-2002 The PHP Group                                |
  +----------------------------------------------------------------------+
  | This source file is subject to version 2.02 of the PHP license,      |
  | that is bundled with this package in the file LICENSE, and is        |
  | available at through the world-wide-web at                           |
  | http://www.php.net/license/2_02.txt.                                 |
  | If you did not receive a copy of the PHP license and are unable to   |
  | obtain it through the world-wide-web, please send a note to          |
  | license@php.net so we can mail you a copy immediately.               |
  +----------------------------------------------------------------------+
  | Author: Stig S�ther Bakken <ssb@fast.no>                             |
  +----------------------------------------------------------------------+

  $Id$ 
*/

require_once "PEAR.php";

class PEAR_Frontend_CLI extends PEAR
{
    // {{{ properties

    var $omode = 'plain';
    var $params = array();
    var $term = array(
        'bold' => '',
        'normal' => '',
        );

    // }}}

    // {{{ constructor

    function PEAR_Frontend_CLI()
    {
        parent::PEAR();

        if (isset($_ENV['TERM'])) {
            // XXX can use ncurses extension here, if available
            if (preg_match('/^(xterm|vt220)/', $_ENV['TERM'])) {
                $this->term['bold'] = sprintf("%c%c%c%c", 27, 91, 49, 109);
                $this->term['normal']=sprintf("%c%c%c", 27, 91, 109);
            } elseif (preg_match('/^vt100/', $_ENV['TERM'])) {
                $this->term['bold'] = sprintf("%c%c%c%c%c%c", 27, 91, 49, 109, 0, 0);
                $this->term['normal']=sprintf("%c%c%c%c%c", 27, 91, 109, 0, 0);
            }
        } elseif (OS_WINDOWS) {
            // XXX add ANSI codes here
        }
    }

    // }}}

    // For now, all the display functions print a "| " at the
    // beginning of the line.  This is just a temporary thing, it
    // is for discovering commands that use print instead of
    // the UI layer.

    // {{{ displayLine(text)

    function displayLine($text)
    {
        print "| $text\n";
    }

    // }}}
    // {{{ displayHeading(title)

    function displayHeading($title)
    {
        print "| ".$this->bold($title)."\n";
        print "| ".str_repeat("=", strlen($title))."\n";
    }

    // }}}
    // {{{ userDialog(prompt, [type], [default])

    function userDialog($prompt, $type = 'text', $default = '')
    {
        if ($type == 'password') {
            system('stty -echo');
        }
        print "| $prompt ";
        if ($default) {
            print "[$default] ";
        }
        print ": ";
        $fp = fopen("php://stdin", "r");
        $line = fgets($fp, 2048);
        fclose($fp);
        if ($type == 'password') {
            system('stty echo');
            print "\n";
        }
        if ($default && trim($line) == "") {
            return $default;
        }
        return $line;
    }

    // }}}
    // {{{ userConfirm(prompt, [default])

    function userConfirm($prompt, $default = 'yes')
    {
        static $positives = array('y', 'yes', 'on', '1');
        static $negatives = array('n', 'no', 'off', '0');
        print "| $prompt [$default] : ";
        $fp = fopen("php://stdin", "r");
        $line = fgets($fp, 2048);
        fclose($fp);
        $answer = strtolower(trim($line));
        if (empty($answer)) {
            $answer = $default;
        }
        if (in_array($answer, $positives)) {
            return true;
        }
        if (in_array($answer, $negatives)) {
            return false;
        }
        if (in_array($default, $positives)) {
            return true;
        }
        return false;
    }

    // }}}
    // {{{ startTable([params])

    function startTable($params = array())
    {
        $this->omode = 'table';
        $params['table_data'] = array();
        $params['widest'] = array();  // indexed by column
        $params['highest'] = array(); // indexed by row
        $params['ncols'] = 0;
        $this->params = $params;
    }

    // }}}
    // {{{ tableRow(columns, [rowparams], [colparams])

    function tableRow($columns, $rowparams = array(), $colparams = array())
    {
        $highest = 1;
        for ($i = 0; $i < sizeof($columns); $i++) {
            $col = $columns[$i];
            if (isset($colparams[$i]) && !empty($colparams[$i]['wrap'])) {
                $col = wordwrap($col, $colparams[$i]['wrap'], "\n", 1);
            }
            if (strpos($col, "\n") !== false) {
                $multiline = explode("\n", $col);
                $w = 0;
                foreach ($multiline as $n => $line) {
                    if (strlen($line) > $w) {
                        $w = strlen($line);
                    }
                }
                $lines = sizeof($lines);
            } else {
                $w = strlen($col);
            }
            if ($w > @$this->params['widest'][$i]) {
                $this->params['widest'][$i] = $w;
            }
            $tmp = count_chars($columns[$i], 1);
            // handle unix, mac and windows formats
            $lines = (isset($tmp[10]) ? $tmp[10] : @$tmp[13]) + 1;
            if ($lines > $highest) {
                $highest = $lines;
            }
        }
        if (sizeof($columns) > $this->params['ncols']) {
            $this->params['ncols'] = sizeof($columns);
        }
        $new_row = array(
            'data' => $columns,
            'height' => $highest,
            'rowparams' => $rowparams,
            'colparams' => $colparams,
            );
        $this->params['table_data'][] = $new_row;
    }

    // }}}
    // {{{ endTable()

    function endTable()
    {
        $this->omode = '';
        extract($this->params);
        if (!empty($caption)) {
            $this->displayHeading($caption);
        }
        if (!isset($width)) {
            $width = $widest;
        } else {
            for ($i = 0; $i < $ncols; $i++) {
                if (!isset($width[$i])) {
                    $width[$i] = $widest[$i];
                }
            }
        }
        if (empty($border)) {
            $cellstart = '';
            $cellend = ' ';
            $rowend = '';
            $padrowend = false;
            $borderline = '';
        } else {
            $cellstart = '| ';
            $cellend = ' ';
            $rowend = '|';
            $padrowend = true;
            $borderline = '+';
            foreach ($width as $w) {
                $borderline .= str_repeat('-', $w + strlen($cellstart) + strlen($cellend) - 1);
                $borderline .= '+';
            }
        }
        if ($borderline) {
            $this->displayLine($borderline);
        }
        for ($i = 0; $i < sizeof($table_data); $i++) {
            extract($table_data[$i]);
            $rowlines = array();
            if ($height > 1) {
                for ($c = 0; $c < sizeof($data); $c++) {
                    $rowlines[$c] = preg_split('/(\r?\n|\r)/', $data[$c]);
                    if (sizeof($rowlines[$c]) < $height) {
                        $rowlines[$c] = array_pad($rowlines[$c], $height, '');
                    }
                }
            } else {
                for ($c = 0; $c < sizeof($data); $c++) {
                    $rowlines[$c] = array($data[$c]);
                }
            }
            for ($r = 0; $r < $height; $r++) {
                $rowtext = '';
                for ($c = 0; $c < sizeof($data); $c++) {
                    if (isset($colparams[$c])) {
                        $attribs = array_merge($rowparams, $colparams);
                    } else {
                        $attribs = $rowparams;
                    }
                    $w = $width[$c];
                    //$cell = $data[$c];
                    $cell = $rowlines[$c][$r];
                    $l = strlen($cell);
                    if ($l > $w) {
                        $cell = substr($cell, 0, $w);
                    }
                    if (isset($attribs['bold'])) {
                        $cell = $this->bold($cell);
                    }
                    if ($l < $w) {
                        // not using str_pad here because we may
                        // add bold escape characters to $cell
                        $cell .= str_repeat(' ', $w - $l);
                    }
                    
                    $rowtext .= $cellstart . $cell . $cellend;
                }
                $rowtext .= $rowend;
                $this->displayLine($rowtext);
            }
        }
        if ($borderline) {
            $this->displayLine($borderline);
        }
    }

    // }}}
    // {{{ bold($text)

    function bold($text)
    {
        if (empty($this->term['bold'])) {
            return strtoupper($text);
        }
        return $this->term['bold'] . $text . $this->term['normal'];
    }

    // }}}
}

?>