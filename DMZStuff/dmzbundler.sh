#!/bin/bash

tar -cvf /home/it490-vm/DeployStuff/IT490-004/DMZStuff/TheTarFile/"DMZBundle".tar /home/it490-vm/DeployStuff/IT490-004/DMZStuff/FilesToBundle

sshpass -p "ubuntu" scp /home/it490-vm/DeployStuff/IT490-004/DMZStuff/TheTarFile/"DMZBundle".tar it490-vm@100.88.147.61:/home/it490-vm/TheBundles

echo "Tar file created"
