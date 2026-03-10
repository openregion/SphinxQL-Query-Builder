#!/bin/sh

if command -v sudo >/dev/null 2>&1; then
  as_root() {
    sudo "$@"
  }
else
  as_root() {
    "$@"
  }
fi

case $SEARCH_BUILD in
  SPHINX2)
    wget --quiet https://sphinxsearch.com/files/sphinx-2.3.2-beta.tar.gz
    tar zxvf sphinx-2.3.2-beta.tar.gz
    cd sphinx-2.3.2-beta
    ./configure --prefix=/usr/local/sphinx --without-mysql
    as_root make && as_root make install
    ;;
  SPHINX3)
    wget --quiet https://sphinxsearch.com/files/sphinx-3.9.1-141d2ea-linux-amd64.tar.gz
    tar zxvf sphinx-3.9.1-141d2ea-linux-amd64.tar.gz
    ;;
  MANTICORE)
    wget --quiet -O search.deb https://github.com/manticoresoftware/manticoresearch/releases/download/2.6.3/manticore_2.6.3-180328-cccb538-release-stemmer.trusty_amd64-bin.deb
    dpkg -x search.deb .
    ;;
esac
