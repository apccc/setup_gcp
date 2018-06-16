# setup_gcp
Setup System for the Google Cloud Platform

# install
From a New Google Cloud Debian VM with a subdomain DNS pointed at the VM:
1) sudo apt-get update;sudo apt-get -yqq install git
2) cd ~;git clone https://github.com/apccc/setup_gcp.git
3) ~/setup_gcp/scripts/buildLocalServer/setup.sh

# transfer core files to other Google VM
e.g.:
~/setup_gcp/scripts/tools/datamanagement/transferLocalFilesToInstance.sh InstanceName

# restore directory from Google Cloud Storage backup
e.g.:
~/setup_gcp/scripts/tools/datamanagement/transferGCSBackupFilesToFolder.sh zstore_subdomain_com_home/7.home.user.tar.gz /home/

# (re)build database from backup
e.g.:
~/setup_gcp/scripts/tools/mysql/ingestFile.sh ~/mysql_backup/localhost/dbdump.day.5.sql.gz

# build local slave from master backup file
~/setup_gcp/scripts/tools/mysql/buildLocalSlaveFromMasterBackupFile.sh ~/dbdump.day.5.sql.gz
- (setup configuration file must have database replication configured correctly)

# setup local database as replication master
~/setup_gcp/scripts/buildLocalServer/mysql/set.replication.master.sh
