#!/bin/bash

version=$1

mv /home/it490-vm/TheBundles/"DMZBundle".tar /home/it490-vm/TheBundles/"DMZBundle${version}".tar

echo "Tar File Version ${version}"
