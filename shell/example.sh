#!/bin/bash

# 脚本参数
# 默认
DEPLOY_ENV='dev';
# 获取
ARGV=`getopt -a -o "" -l deploy-env: -- "$@"`;
if [ $? != 0 ]
then
	echo 'Usage: ...'
	exit 2
fi
eval set -- "$ARGV";
while true
do
	case "$1" in
		--deploy-env) DEPLOY_ENV=$2; shift;;
		--) shift;break;;
	esac
	shift
done

# shell 目录 [  cd 相对路径 -> 获取绝对路径 ]
cd `dirname $0`;
SHELL_DIR=`pwd`;
# application 目录
APP_DIR=`dirname $SHELL_DIR`;
# public 目录
PUBLIC_DIR=${APP_DIR}'/public';
# html 目录
HTML_DIR=${PUBLIC_DIR}'/html';

