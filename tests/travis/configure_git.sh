#!/bin/bash

if [ "$TRAVIS_COMMITTER_EMAIL" == "" ]; then
    TRAVIS_COMMITTER_EMAIL="hello@piwik.org"
fi

if [ "$TRAVIS_COMMITTER_NAME" == "" ]; then
    TRAVIS_COMMITTER_NAME="Piwik Automation"
fi

echo "Configuring git [email = $TRAVIS_COMMITTER_EMAIL, user = $TRAVIS_COMMITTER_NAME]..."

git config --global user.email "$TRAVIS_COMMITTER_EMAIL"
git config --global user.name "$TRAVIS_COMMITTER_NAME"

# Install git lfs
# TODO: remove when Travis updates the VM (should be installed by default)
curl -sLo - https://github.com/github/git-lfs/releases/download/v0.5.1/git-lfs-linux-amd64-0.5.1.tar.gz | tar xzvf -
cd git-lfs-*
./install.sh
