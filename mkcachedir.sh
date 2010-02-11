#!/bin/bash
echo Creating cache directory and assigning it world-writable rights.
mkdir cache
mkdir cache/small
mkdir cache/large
chmod 777 cache cache/small cache/large
touch cache/index.html cache/small/index.html cache/large/index.html
