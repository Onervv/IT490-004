#!/bin/bash

tar -cvf /home/it490-vm/TestExtract/da_archive.tar /home/it490-vm/TestBundle/rabbitmqphp_example

sshpass -p "insert_password" scp /home/it490-vm/TestExtract/da_archive.tar "user_name"@"ip_address":/home/it490-vm/TestSCP

echo "Tar file created"
