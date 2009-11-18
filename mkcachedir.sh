#!/bin/bash
echo Creating cache directory and assigning it world-writable rights.
mkdir cache
mkdir cache/small
mkdir cache/medium
mkdir cache/large
chmod 777 cache
chmod 777 cache/*
