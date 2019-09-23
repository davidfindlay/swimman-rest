#!/usr/bin/env bash
set -e
cp .env .env-bak
cp .env-prod .env
tar -pczf swimman-rest.tar.gz .
scp -P 8022 swimman-rest.tar.gz davsoft@davsoft.com.au:msqprod.davsoft.com.au/
ssh -p 8022 davsoft@davsoft.com.au << EOF
cd msqprod.davsoft.com.au
tar -zxvf swimman-rest.tar.gz
rm swimman-rest.tar.gz
/hsphere/shared/php71/bin/php-cli artisan migrate
EOF
rm swimman-rest.tar.gz
rm .env
cp .env-bak .env