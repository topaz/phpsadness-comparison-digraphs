<?php

$noeq = $argv[1] === 'noeq';

$vals = array(
  '-1'    => -1,
  '""'    => "",
  '"\\0"' => "\0",
  '"1"'   => "1",
  '"a"'   => "a",
  '"b"'   => "b",
  '[]'    => array(),
  '[0]'   => array(0),
  '[1]'   => array(1),
  '[0,1]' => array(0,1),
  '{}'            => (object)(array()),
  '{a:"b"}'       => (object)(array("a"=>"b")),
  '{a:"b",b:"a"}' => (object)(array("a"=>"b","b"=>"a")),
  'NULL/FALSE' => FALSE,
  '-INF'  => -INF,
  '0'     => 0,
  '1'     => 1,
  'INF'   => INF,
  'TRUE'  => TRUE,
);

$vnames = array_keys($vals);
$vn = count($vnames);

echo "digraph G {\nconcentrate=false;\n";
for ($i=0; $i<$vn; $i++) {
  echo "v$i [label=\"" . str_replace("\"","\\\"",$vnames[$i]) . "\"];\n";
}

for ($a=0; $a<$vn-1; $a++) {
  $n1 = $vnames[$a];
  $v1 = $vals[$n1];
  for ($b=$a+1; $b<$vn; $b++) {
    $n2 = $vnames[$b];
    $v2 = $vals[$n2];
    compare("v$a", $v1, "v$b", $v2);
  }
}

echo "}\n";

function compare($n1, $v1, $n2, $v2) {
  global $noeq;
  if ($v1 < $v2) { echo "$n1 -> $n2;\n"; }
  if ($v1 > $v2) { echo "$n2 -> $n1;\n"; }
  if (!$noeq && $v1 == $v2) { echo "$n1 -> $n2 [dir=none,color=\"#009900\"];\n"; }
}
