#!/bin/bash

cd $(dirname "$0")

# Install code coverage tools if applicable and disable XDebug otherwise
if [ -n "$COVERAGE" ]
then
	# Install Scrutinizer's external code coverage tool
	echo "Installing Scrutinizer"
	sh -c "./installScrutinizer.sh 2>&1 &" >/dev/null 2>&1 &
else
	echo "Removing XDebug"
	phpenv config-rm xdebug.ini
fi

# Install Composer dependencies after XDebug has been removed
echo "Installing Composer dependencies"
./installComposer.sh 2>&1 &

# Install Closure Compiler
echo "Installing Closure Compiler"
./installClosureCompiler.sh >/dev/null 2>&1 &

# The cache dir lets the MediaEmbed plugin cache scraped content
mkdir ../../tests/.cache

echo "Starting webserver"
php -S localhost:8000 -d "always_populate_raw_post_data=-1" -t ../../tests 2>/dev/null &