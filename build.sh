#!/bin/bash

type -P php     >/dev/null 2>&1 || { echo 'PHP is required to build the graphviz files! (missing `php`)'; exit 1; }
type -P dot     >/dev/null 2>&1 || { echo 'Graphviz is required to render the graphviz files! (missing `dot`)'; exit 1; }
type -P tred    >/dev/null 2>&1 || { echo 'Graphviz is required to render the graphviz files! (missing `tred`)'; exit 1; }
type -P convert >/dev/null 2>&1 || { echo 'ImageMagick is required to optimize the images! (missing `convert`)'; exit 1; }
type -P pngquant >/dev/null 2>&1 || { echo 'pngquant is required to optimize the images! (missing `pngquant`)'; exit 1; }

echo "Building order-full-eq.dot..."
php order-full.php > order-full-eq.dot

echo "Building order-full-noeq.dot..."
php order-full.php noeq > order-full-noeq.dot

echo "Computing transitive reduction of order-full-noeq.dot..."
echo -ne "\e[31m" #colorize warnings from tred
tred order-full-noeq.dot > order-full-noeq-tred.dot
echo -ne "\e[0m" #back to no colors

echo "Building order-clean.dot..."
php order-clean.php > order-clean.dot

for thing in order-full-eq order-full-noeq order-full-noeq-tred order-clean
do
  echo "Rendering $thing..."
  dot -Tpng $thing.dot > $thing.png

  echo "  ...and resizing..."
  pngquant 256 < $thing.png > $thing.8.png
  mv $thing.8.png $thing.png

  echo "  ...and making thumbnail..."
  convert $thing.png -thumbnail 400x400 $thing-thumb.png
  pngquant 256 < $thing-thumb.png > $thing-thumb.8.png
  mv $thing-thumb.8.png $thing-thumb.png
done

echo "Done!"
