<?php

# the well-ordered set of values on which we can imply a transitive reduction
$ordered = array(
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
);
$onames = array_keys($ordered);
$on = count($onames);

# assert that the ordered set is actually well-ordered
for ($a=0; $a<$on-1; $a++) {
  $n1 = $onames[$a];
  $v1 = $ordered[$n1];
  for ($b=$a+1; $b<$on; $b++) {
    $n2 = $onames[$b];
    $v2 = $ordered[$n2];
    if ($v1>$v2 || !($v1<$v2) || $v1==$v2) {
      die("well-ordered set isn't well-ordered! ($n1 <=> $n2)");
    }
  }
}

# extra, unordered values
$vals = array(
  'NULL/FALSE' => FALSE,
  '-INF'  => -INF,
  '0'     => 0,
  '1'     => 1,
  'INF'   => INF,
  'TRUE'  => TRUE,
);

$vnames = array_keys($vals);
$vn = count($vnames);
$vpos = array_fill(0, $vn, NULL);

echo <<<EOF
digraph G {
  concentrate=true;
  edge [arrowhead=invempty];
EOF;
# draw the well-ordered set
for ($i=0; $i<$on; $i++) {
  echo "o$i [color=\"#9999ff\", style=filled, label=\"".gv_esc($onames[$i])."\"];\n";
  if ($i>0) {
    echo "o".($i-1).":s -> o$i:n [color=\"#3333ff\"];\n";
  }
}
# set up the unordered nodes
for ($i=0; $i<$vn; $i++) {
  echo "v$i [label=\"".gv_esc($vnames[$i])."\"];\n";
}

# place the unordered nodes in the ordered set
for ($a=0; $a<$vn; $a++) {
  $av = $vals[$vnames[$a]];
  # search until b is the first o# greater than a
  for ($b=0; $b<$on; $b++) {
    if ($av < $ordered[$onames[$b]]) {
      $vpos[$a] = $b;
      break;
    }
  }
  # show relationships for future nonequal nodes
  if ($b < $on) {
    # link to the first previous ordered node less than a (in case we skipped some equal ones, as for 0 <=> "1")
    for ($i=$b-1; $i>=0; $i--) {
      if ($av > $ordered[$onames[$i]]) {
        echo "o$i:s -> v$a:n [color=orange];\n";
        break;
      }
    }
    echo "v$a:s -> o$b:n [color=orange];\n";
    for ($b=$b+1; $b<$on; $b++) {
      if ($av > $ordered[$onames[$b]]) {
        echo "o$b:ne -> v$a [color=red,constraint=false];\n";
      }
    }
  }
  # show "==" relationships for any equal nodes
  for ($b=0; $b<$on; $b++) {
    gv_eq($av, $ordered[$onames[$b]], "o$b", "v$a");
  }
}

# draw remaining links between the unordered nodes
for ($a=0; $a<$vn-1; $a++) {
  $n1 = $vnames[$a];
  $v1 = $vals[$n1];
  for ($b=$a+1; $b<$vn; $b++) {
    $n2 = $vnames[$b];
    $v2 = $vals[$n2];
    if ($v1 < $v2 && $vpos[$a] >= $vpos[$b]) { echo "v$a:se -> v$b:nw;\n"; }
    if ($v1 > $v2 && $vpos[$a] <= $vpos[$b]) { echo "v$b:se -> v$a:nw;\n"; }
    gv_eq($v1, $v2, "v$a", "v$b");
  }
}

echo <<<EOF
  subgraph cluster_legend {
    label = "Legend";

    l1 [label="b"]
    l2 [label="a"]
    l1 -> l2 [constraint=false,label="a < b"];

    l3 [label="c", color="#9999ff", style=filled];
    l4 [label="d", color="#9999ff", style=filled];
    l5 [label="e", color="#9999ff", style=filled];
    l1 -> l3 [style=invis];
    l1 -> l4 [style=invis];
    l1 -> l5 [style=invis];
    l3 -> l4 [constraint=false,color="#3333ff",label="c < d"];
    l4 -> l5 [constraint=false,color="#3333ff",label="d < e"];

    l6 [shape=none,fontcolor="#3333ff",label="Blue also implies complete transitivity: c < e"];
    l4 -> l6 [style=invis];

    l7 [label="f", color="#9999ff", style=filled];
    l8 [label="g"];
    l9 [label="h", color="#9999ff", style=filled];
    l6 -> l7 [style=invis];
    l6 -> l8 [style=invis];
    l6 -> l9 [style=invis];
    l7 -> l8 [constraint=false,color=orange,label="f < g"];
    l8 -> l9 [constraint=false,color=orange,label="g < h"];

    l10 [shape=none,fontcolor=orange,label="Orange indicates inclusion in the blue\\ngroup other than these exceptions:"];
    l8 -> l10 [style=invis];

    l11 [label="i"]
    l12 [label="j"]
    l11 -> l12 [constraint=false,label="i == j",dir=none,color="#66ff66"];
    l11 -> l12 [constraint=false,dir=none,color="#006600"];
    l10 -> l11 [style=invis];
    l10 -> l12 [style=invis];

    l13 [label="k"]
    l14 [label="l"]
    l13 -> l14 [constraint=false,label="k < l",color=red];
    l12 -> l13 [style=invis];
    l12 -> l14 [style=invis];

    l15 [shape=none,label="\\"[...]\\" means \\"array(...)\\""];
    l14 -> l15 [style=invis];

    l16 [shape=none,label="\\"{k:'v'}\\" means \\"(object) array('k' => 'v')\\""];
    l15 -> l16 [style=invis];
  }
EOF;

echo "}\n";

function gv_esc($str) {
  return str_replace(array("\\","\""),array("\\\\","\\\""),$str);
}

function gv_eq($v1, $v2, $n1, $n2) {
  if ($v1 == $v2) {
    if ($v1 === TRUE || $v2 === TRUE) {
      $c = "#66ff66";
    } else if ($v1 === 1 || $v2 === 1) {
      $c = "#00ff00";
    } else {
      $c = "#006600";
    }
    echo "$n1 -> $n2 [dir=none,color=\"$c\",constraint=false];\n";
  }
}
