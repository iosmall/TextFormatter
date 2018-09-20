#!/bin/bash

cd "$(dirname $0)"/../..
if [ ! -d vendor ]
then
	mkdir vendor
fi
cd vendor
npm i google-closure-compiler-linux

wget -O - http://dl.google.com/closure-compiler/compiler-latest.tar.gz | tar xzf - -C/tmp && \
mv /tmp/closure-compiler-*.jar /tmp/compiler.jar