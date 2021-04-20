#!/bin/bash

CUR_PATH="`dirname \"$0\"`"

cd "$CUR_PATH/.."

xgettext *.php */*.php -o locales/actualtime.pot -L PHP --add-comments=TRANS --from-code=UTF-8 --force-po -k --keyword=__:1,2t --keyword=_x:1,2,3t --keyword=__s:1,2t --keyword=_sx:1,2,3t --keyword=_n:1,2,3,4t --keyword=_sn:1,2t --keyword=_nx:1,2,3t --copyright-holder "TICgal" --package-name "Actualtime Plugin" --package-version "1.1.0" --msgid-bugs-address=https://github.com/ticgal/actualtime/issues

cd locales

sed -i "s/SOME DESCRIPTIVE TITLE/ActualTime Glpi Plugin/" actualtime.pot
sed -i "s/FIRST AUTHOR <EMAIL@ADDRESS>, YEAR./TICgal, $(date +%Y)/" actualtime.pot
sed -i "s/YEAR/$(date +%Y)/" actualtime.pot

for a in $(ls *.po); do
	msgmerge -U $a actualtime.pot
	msgfmt $a -o "${a%.*}.mo"
done
rm -f *.po~
