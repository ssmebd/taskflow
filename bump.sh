#!/bin/sh
# Run before each commit: sets the build number (= number of commits after this one) in api.php and index.php.
N=$(( $(git rev-list --count HEAD) + 1 ))
sed -i "s/^const BUILD=[0-9]*;/const BUILD=$N;/" api.php
sed -i "s/^const BUILD=[0-9]*;/const BUILD=$N;/" index.php
echo $N
