#!/bin/bash

DBUSER=root
DBPASS=5ubnQYzko_
DBNAME=shokunin

echo "Downloading..."
wget -q http://www.post.japanpost.jp/zipcode/dl/kogaki/zip/ken_all.zip

echo 'Melting...'
unzip -o -q ken_all.zip

echo "Convert SJIS to UTF8"
nkf -w KEN_ALL.CSV > mst_address.csv

# delete base file
rm -f KEN_ALL.CSV

echo 'Importing to MySQL'
mysqlimport -s -u ${DBUSER} -p${DBPASS} --local --delete --fields-terminated-by=, --fields-enclosed-by="\"" --fields-escaped-by="\\" --lines-terminated-by="\r\n" ${DBNAME} mst_address.csv;
mysql -u ${DBUSER} -p${DBPASS} ${DBNAME} -e "TRUNCATE mst_address_mini;INSERT INTO mst_address_mini SELECT officialCode, pref, city FROM mst_address GROUP BY officialCode"

# delete CSV
rm -f KEN_ALL.CSV
rm -f mst_address.csv
rm -f ken_all.zip


