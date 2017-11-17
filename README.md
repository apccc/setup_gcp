# setup_gcp
Setup System for the Google Cloud Platform

# install
From a New Google Cloud Debian VM with a subdomain DNS pointed at the VM:
1) sudo apt-get update;sudo apt-get -yqq install git
2) cd ~;git clone https://github.com/apccc/setup_gcp.git
3) ~/setup_gcp/scripts/buildLocalServer/setup.sh

# restore directory from Google Cloud Storage backup
e.g.:
~/setup_gcp/scripts/tools/datamanagement/transferGCSBackupFilesToFolder.sh zstore_subdomain_com_home/7.home.user.tar.gz /home/
