#!/usr/bin/env bash
set -e
cp .env .env-bak
cp .env-prod .env
tar -pczf swimman-rest.tar.gz .
scp -i ~/Dropbox/SSH/MyEC2KeyPair.pem swimman-rest.tar.gz ec2-user@api.quickentry.mastersswimmingqld.org.au:~/
ssh -i ~/Dropbox/SSH/MyEC2KeyPair.pem ec2-user@api.quickentry.mastersswimmingqld.org.au << EOF
cp swimman-rest.tar.gz /var/www/html/
cd /var/www/html/
tar -zxvf swimman-rest.tar.gz
rm swimman-rest.tar.gz
php artisan migrate
sudo chown -R ec2-user:apache *
sudo chmod -R 775 *
EOF
rm swimman-rest.tar.gz
rm .env
cp .env-bak .env