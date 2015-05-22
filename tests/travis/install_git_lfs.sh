#!/bin/bash

# Install git lfs
# TODO: remove when Travis updates the VM (should be installed by default)
curl -sLo - https://github.com/github/git-lfs/releases/download/v0.5.1/git-lfs-linux-amd64-0.5.1.tar.gz | tar xzvf -
cd git-lfs-*
sudo ./install.sh
cd ..
rm -rf git-lfs-*

# Now that git-lfs is installed we update the repo to download the screenshots
# See https://github.com/github/git-lfs/issues/325
rm tests/UI/expected-screenshots/*.png
sudo git checkout .
