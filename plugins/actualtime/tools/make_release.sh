#!/bin/bash
#
# -------------------------------------------------------------------------
# make_release.sh
# Based on fusioninventory-for-glpi make_release.sh
# Copyright (C) 2018-2019 by TICgal 
# https://github.com/ticgal/actualtime
# -------------------------------------------------------------------------
# LICENSE
# This file is part of the actualtime plugin.
# actualtime plugin is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 3 of the License, or
# (at your option) any later version.
# actualtime plugin is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# You should have received a copy of the GNU General Public License
# along with actualtime. If not, see <http://www.gnu.org/licenses/>.
# --------------------------------------------------------------------------
# @package   actualtime
# @author    TICgal
# @copyright Copyright (c) 2018-2019 TICgal
# @license   AGPL License 3.0 or (at your option) any later version
#            http://www.gnu.org/licenses/agpl-3.0-standalone.html
# @link      https://tic.gal
# @since     2018
# --------------------------------------------------------------------------

PLUGINNAME="actualtime"

if [ ! "$#" -eq 2 ]
then
    echo "Usage $0 fi_git_dir release"
    exit
fi

read -p "Are translations up to date? [Y/n] " -n 1 -r
echo    # (optional) move to a new line
if [[ ! $REPLY =~ ^[Yy]$ ]] 
    then
    [[ "$0" = "$BASH_SOURCE" ]] && exit 1 || return 1 # handle exits from shell or function but don't exit interactive shell
fi

INIT_DIR=$1
RELEASE=$2

# remove old tmp files
if [ ! -e /tmp/$PLUGINNAME ]
then
    echo "Deleting temp directory"
    rm -rf /tmp/$PLUGINNAME
fi

# test plugin_cvs_dir
if [ ! -e $INIT_DIR ] 
then
    echo "$1 does not exist"
    exit 
fi

INIT_PWD=$PWD;

if [ -e /tmp/$PLUGINNAME ]
then
    echo "Delete existing temp directory"
    rm -rf /tmp/$PLUGINNAME
fi

echo "Copy to  /tmp directory"
git checkout-index -a -f --prefix=/tmp/$PLUGINNAME/

echo "Move to this directory"
cd /tmp/$PLUGINNAME

echo "Check version"
if grep --quiet $RELEASE setup.php; then
    echo "$RELEASE found in setup.php, OK."
else
    echo "$RELEASE has not been found in setup.php. Exiting."
    exit 1
fi

echo "Compile locale files"
./tools/generate_locales.sh

echo "Delete various scripts and directories"
rm -rf vendor
rm -rf RoboFile.php
rm -rf tools
rm -rf phpunit
rm -rf tests
rm -rf .gitignore
rm -rf .travis.yml
rm -rf .coveralls.yml
rm -rf phpunit.xml.dist
rm -rf composer.json
rm -rf composer.lock
rm -rf .composer.hash
rm -rf ISSUE_TEMPLATE.md
rm -rf PULL_REQUEST_TEMPLATE.md
rm -rf .tx
rm -rf $PLUGINNAME.xml
rm -rf screenshots

echo "Creating tarball"
cd ..
tar czf "$PLUGINNAME-$RELEASE.tar.tgz" $PLUGINNAME

cd $INIT_PWD;

echo "Deleting temp directory"
rm -rf /tmp/$PLUGINNAME

echo "The Tarball is in the /tmp directory"
