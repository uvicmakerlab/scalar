<?php
/**
 * ARC2 RDF/JSON Serializer
 *
 * @author Benjamin Nowack <bnowack@semsol.com>
 * @license http://arc.semsol.org/license
 * @homepage <http://arc.semsol.org/>
 * @package ARC2
 * @version 2010-11-16
*/

ARC2::inc('RDFSerializer');

class ARC2_RDFJSONSerializer extends ARC2_RDFSerializer {

  function __construct($a, &$caller) {
    parent::__construct($a, $caller);
  }
  
  function __init() {
    parent::__init();
    $this->content_header = 'application/json';
  }

  /*  */
  
  function getTerm($v, $term = 's') {
    if (!is_array($v)) {
      if (preg_match('/^\_\:/', $v)) {
        return ($term == 'o') ? $this->getTerm(array('value' => $v, 'type' => 'bnode'), 'o') : '"' . $v . '"';
      }
      return ($term == 'o') ? $this->getTerm(array('value' => $v, 'type' => 'uri'), 'o') : '"' . $v . '"';
    }
    if (!isset($v['type']) || ($v['type'] != 'literal')) {
      if ($term != 'o') {
        return $this->getTerm($v['value'], $term);
      }
      if (preg_match('/^\_\:/', $v['value'])) {
        return '{ "value" : "' . $this->jsonEscape($v['value']) . '", "type" : "bnode" }';
      }
      return '{ "value" : "' . $this->jsonEscape($v['value']) . '", "type" : "uri" }';
    }
    /* literal */
    $r = '{ "value" : "' . $this->jsonEscape($v['value']) . '", "type" : "literal"';
    $suffix = isset($v['datatype']) ? ', "datatype" : "' . $v['datatype'] . '"' : '';
    $suffix = isset($v['lang']) ? ', "lang" : "' . $v['lang'] . '"' : $suffix;
    $r .= $suffix . ' }';
    return $r;
  }

  function jsonEscape($v) {
  	/*
    if (function_exists('json_encode')) { // Updated by Craig
    	$v = json_encode($v);
 		if ('"'==substr($v,0,1)&&'"'==substr($v,-1,1)) $v = substr($v, 1, -1);
 		return $v;   	
    }
    */
	if (function_exists('json_encode')) { // Updated by KNurg (GitHub)
		$v = json_encode($v);
		if(preg_match('/^"(.*)"$/', $v, $matches)) $v = $matches[1];
		return $v;
	}    
    $from = array("\\", "\r", "\t", "\n", '"', "\b", "\f", "/");
    $to = array('\\\\', '\r', '\t', '\n', '\"', '\b', '\f', '\/');
    return str_replace($from, $to, $v);
  }
    
  function getSerializedIndex($index, $raw = 0) {
    $r = '';
    $nl = "\n";
    foreach ($index as $s => $ps) {
      $r .= $r ? ',' . $nl . $nl : '';
      $r .= '  ' . $this->getTerm($s). ' : {';
      $first_p = 1;
      foreach ($ps as $p => $os) {
        $r .= $first_p ? $nl : ',' . $nl;
        $r .= '    ' . $this->getTerm($p). ' : [';
        $first_o = 1;
        if (!is_array($os)) {/* single literal o */
          $os = array(array('value' => $os, 'type' => 'literal'));
        }
        foreach ($os as $o) {
          $r .= $first_o ? $nl : ',' . $nl;
          $r .= '      ' . $this->getTerm($o, 'o');
          $first_o = 0;
        }
        $first_p = 0;
        $r .= $nl . '    ]';
      }
      $r .= $nl . '  }';
    }
    $r .= $r ? ' ' : '';
    return '{' . $nl . $r . $nl . '}';
  }
  
  /*  */

}
