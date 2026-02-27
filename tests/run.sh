#!/bin/sh

case $SEARCH_BUILD in
  SPHINX2)
    WORK=$HOME/search
    gcc -shared -o data/test_udf.so test_udf.c
    /usr/local/sphinx/bin/searchd -c sphinx.conf
    ;;
  SPHINX3)
    WORK=$HOME/search/sphinx-3.0.3
    UDF_SRC="$WORK/src/udfexample.c"
    if [ ! -f "$UDF_SRC" ]; then
      UDF_SRC=$(find "$HOME/search" -path '*/src/udfexample.c' | head -n 1)
    fi
    if [ -z "$UDF_SRC" ] || [ ! -f "$UDF_SRC" ]; then
      echo "Unable to find udfexample.c for SPHINX3 build."
      exit 1
    fi
    gcc -shared -o data/test_udf.so "$UDF_SRC"
    "$WORK/bin/searchd" -c sphinx.conf
    ;;
  MANTICORE)
    WORK=$HOME/search
    gcc -shared -o data/test_udf.so ms_test_udf.c
    $WORK/usr/bin/searchd -c manticore.conf
    ;;
esac
