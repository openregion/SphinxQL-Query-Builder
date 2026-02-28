#!/bin/sh

mkdir -p data
rm -f data/rt.* data/binlog.*

case $SEARCH_BUILD in
  SPHINX2)
    WORK=$HOME/search
    gcc -shared -o data/test_udf.so test_udf.c
    /usr/local/sphinx/bin/searchd -c sphinx.conf
    ;;
  SPHINX3)
    WORK=$(find "$HOME/search" -maxdepth 1 -type d -name 'sphinx-3.*' | head -n 1)
    if [ -z "$WORK" ] || [ ! -d "$WORK" ]; then
      echo "Unable to find extracted SPHINX3 directory."
      exit 1
    fi
    if [ ! -f "$WORK/src/sphinxudf.h" ]; then
      echo "Unable to find sphinxudf.h for SPHINX3 build."
      exit 1
    fi
    gcc -shared -I"$WORK/src" -o data/test_udf.so s3_test_udf.c
    "$WORK/bin/searchd" -c sphinx.conf
    ;;
  MANTICORE)
    WORK=$HOME/search
    gcc -shared -o data/test_udf.so ms_test_udf.c
    $WORK/usr/bin/searchd -c manticore.conf
    ;;
esac
