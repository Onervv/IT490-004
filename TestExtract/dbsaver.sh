#!/bin/bash

version=$1

mv /home/it490-vm/TheBundles/"DatabaseBundle".tar /home/it490-vm/TheBundles/"DatabaseBundle${version}".tar

echo "Tar File Version ${version}"
