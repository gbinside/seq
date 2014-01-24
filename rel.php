<?php
//require_once 'autoload.php';

$data=file_get_contents('oauth.rel');


#$graph=new Graph();
$graph=new SeqDiag();
$graph->parse($data);
file_put_contents('tempfile.dot',$graph->render());

shell_exec('dot -Tpng -otempfile.png tempfile.dot');
shell_exec('explorer tempfile.png');

class Edge
{
  function __construct()
  {
    $this->subject='';
    $this->verb='';
    $this->object='';
  }
  
  function HTMLverb()
  {
    $html=$this->verb;
    $html=preg_replace('#\s*\\\\n\s*#','<br align="left"/>',$html);
    $html=preg_replace('#<pre>(.*?)</pre>#','<br align="left"/><font face="Courier">\1<br align="left"/></font>',$html);
    return "$html";
  }
}

class Graph
{
  function __construct()
  {
    $this->edges=array();
    $this->is_directional=true;
    $this->layout='dot';
  }

  function append($edge)
  {
    $this->edges[]=$edge;
  }

  function render()
  {
    $di=$this->is_directional?'di':'';
    $arrow=$this->is_directional?'->':'--';
    $ret="{$di}graph smtg {";
    if ($this->layout=='fdp')
      $ret.="layout=fdp;K=2;";
    $ret.="node [fontname=Arial];";
    $ret.="edge [fontname=Arial];";
    foreach($this->edges as $edge)
    {
      $ret.="\"{$edge->subject}\" $arrow \"{$edge->object}\" [label=\"{$edge->verb}\"];";
      
      $subject=ucwords(strtr($edge->subject,'_',' '));
      $object =ucwords(strtr($edge->object,'_',' '));

      $ret.="\"{$edge->subject}\" [label=<{$subject}>];";
      $ret.="\"{$edge->object}\"  [label=<{$object}>];";

    }
    $ret.='}';
    return $ret;
  }

  function parse($data)
  {
    $edge=new Edge();
    
    $ok=preg_match_all('/(\n[ \t\r]*\n)|#(\w+)|(\S+)/',$data,$M,PREG_SET_ORDER);
    if(!$ok)
      return false;

    foreach($M as $m) {
      @list(,$nl,$ret,$word)=$m;
      if($ret) {
        if (!$edge->subject) {
          $edge->subject=$ret;
        } else {
          $edge->object=$ret;
          $this->append($edge);
          $edge=new Edge();
          $edge->subject=$ret;
        }
      }

      if($word) {
        $edge->verb.=($edge->verb?' ':'').$word;
      }

      if($nl) {
        if($edge->verb)
          $this->append($edge);
        $edge=new Edge();
      }
    }
    if($edge->verb)
      $this->append($edge);
  }

  function nodes()
  {
    $hash=array();
    foreach($this->edges as $edge)
    {
      $hash[$edge->subject]=1;
      $hash[$edge->object ]=1;
    }
    return array_keys($hash);
  }
}

class SeqDiag extends Graph
{
  function render()
  {
    $ret='digraph { layout=dot;node [fontname="Arial",shape=circle,label="",width=0.0001, height=0.0001]; edge [fontname="Arial",dir=both,arrowtail=dot,weight=1]; 0 [color=transparent]; ';
  
    foreach($this->nodes() as $node) {
      $ret.="0 -> \"$node\" [weight=2000,color=transparent];";
      $label=ucwords(strtr($node,'_',' '));
      $ret.="\"$node\" [label = \"$label\",shape=box,width=.75, height=.5];";

      $ret.="subgraph \"cluster_$node\" { color=transparent; \"{$node}\"";
      foreach($this->edges as $level=>$edge) {
        $ret.=" -> \"{$node}_l$level\"";
      }
      $ret.=" [weight=3000,arrowhead=none,arrowtail=none]; }";
    }
    
    foreach($this->edges as $level=>$edge) {
      $HTMLverb=$edge->HTMLverb();
      //$ret.="\"{$edge->subject}_l$level\" -> \"{$edge->object}_l$level\" [label=\"{$edge->verb}\"];";
      $ret.="\"{$edge->subject}_l$level\" -> \"{$edge->object}_l$level\" [label=<{$HTMLverb}>];";
    } 

    $ret.='}';
    return $ret;
  }
}
