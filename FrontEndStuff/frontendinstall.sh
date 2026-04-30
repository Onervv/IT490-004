#!/bin/bash

version=$1

sshpass -p "ubuntu" scp it490-vm@100.88.147.61:/home/it490-vm/TheBundles/"FrontendBundle${version}".tar /home/it490-vm/BundleToInstall 

echo "Bundle Installed"
