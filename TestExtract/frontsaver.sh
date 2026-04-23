#!/bin/bash

version=$1

mv /home/it490-vm/TheBundles/"FrontendBundle".tar /home/it490-vm/TheBundles/"FrontendBundle${version}".tar

echo "Tar File Version ${version}"
