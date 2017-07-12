#!/usr/bin/python3
# -*- coding: utf-8 -*-

import argparse
import fileinput
import os
import subprocess

ARGS = argparse.ArgumentParser(description="IQRF daemon webapp installer.")
ARGS.add_argument("-d", "--dist", action="store", dest="dist", required=True, type=str, help="The used linux distribution.")
ARGS.add_argument("-v", "--ver", action="store", dest="ver", required=True, type=str, help="The version of used linux distribution.")

WEBSERVER_DIRECTORY = "/var/www"
WEBAPP_DIRECTORY = WEBSERVER_DIRECTORY + "/iqrf-daemon-webapp"
SUDOERS_FILE = "/etc/sudoers"

def send_command(cmd):
	print(cmd)
	return subprocess.Popen(cmd, shell=True, stdout=subprocess.PIPE).stdout.read()

def main():
	args = ARGS.parse_args()
	dist = args.dist.lower()
	ver = args.ver.lower()

	if dist == "debian" or dist == "raspbian":
		install_debian(ver)
	elif dist == "ubuntu":
		install_ubuntu(ver)

def install_debian(version):
	send_command("apt-get update")
	if version == "8" or version == "jessie" or version == "oldstable":
		# Install sudo, nginx and php5
		send_command("apt-get install -y sudo php5 php5-common php5-fpm php5-curl php5-json php5-sqlite nginx-full")
		# Install composer
		send_command("bash ./install-composer.sh")
		send_command("mv composer.phar /usr/bin/composer")
		chmod_daemon_dir()
		install_php_app(WEBAPP_DIRECTORY)
		disable_default_nginx_virtualhost()
		create_webapp_nginx_virtualhost("iqrf-daemon-webapp_php5.localhost")
		fix_php_fpm_config("/etc/php5/fpm/php.ini")
		chown_webapp_dir(WEBAPP_DIRECTORY)
		enable_sudo()
		restart_service("sudo")
		restart_service("php5-fpm")
		restart_service("nginx")
	elif version == "9" or version == "stretch" or version == "stable":
		# Install sudo, nginx php7.0, composer and zip
		send_command("apt-get install -y sudo php7.0 php7.0-common php7.0-fpm php7.0-curl php7.0-json php7.0-sqlite php7.0-mbstring composer nginx-full zip unzip")
		chmod_daemon_dir()
		install_php_app(WEBAPP_DIRECTORY)
		disable_default_nginx_virtualhost()
		create_webapp_nginx_virtualhost("iqrf-daemon-webapp_php7-0.localhost")
		fix_php_fpm_config("/etc/php/7.0/fpm/php.ini")
		chown_webapp_dir(WEBAPP_DIRECTORY)
		enable_sudo()
		restart_service("sudo")
		restart_service("php7.0-fpm")
		restart_service("nginx")

def install_ubuntu(version):
	send_command("apt-get update")
	if version == "16.04" or version == "xenial" or version == "xerus":
		# Install sudo, nginx php7.0, composer and zip
		send_command("apt-get install -y sudo php7.0 php7.0-common php7.0-fpm php7.0-curl php7.0-json php7.0-sqlite php7.0-mbstring composer nginx-full zip unzip")
		chmod_daemon_dir()
		install_php_app(WEBAPP_DIRECTORY)
		disable_default_nginx_virtualhost()
		create_webapp_nginx_virtualhost("iqrf-daemon-webapp_php7-0.localhost")
		fix_php_fpm_config("/etc/php/7.0/fpm/php.ini")
		chown_webapp_dir(WEBAPP_DIRECTORY)
		enable_sudo()
		restart_service("sudo")
		restart_service("php7.0-fpm")
		restart_service("nginx")

def install_php_app(dir, use_git=True):
	if not os.path.isdir(dir):
		if use_git:
			send_command("cd " + dir + "/../ ; git clone https://github.com/iqrfsdk/iqrf-daemon-webapp")
			send_command("cd " + dir + " ; composer install")
		else:
			send_command("cd " + dir + "/../ ; composer create-project iqrfsdk/iqrf-daemon-webapp")
	else:
		send_command("rm -rf " + dir + "/temp/cache")
		if use_git:
			send_command("cd " + dir + " ; git pull origin")
		else:
			send_command("cd " + dir + " ; composer update")


def chmod_daemon_dir(dir_name="/etc/iqrf-daemon"):
	send_command("chmod -R 666 " + dir_name)
	send_command("chmod 777 " + dir_name)

def chown_webapp_dir(dir_name):
	send_command("chown -R www-data:www-data " + dir_name)

def disable_default_nginx_virtualhost(nginx_dir="/etc/nginx"):
	virtualhost = nginx_dir + "/sites-enabled/default"
	if os.path.isfile(virtualhost):
		os.remove(virtualhost)

def create_webapp_nginx_virtualhost(virtualhost, nginx_dir="/etc/nginx"):
	available_virtualhost = nginx_dir + "/sites-available/iqrf-daemon-webapp.localhost"
	enabled_virtualhost = nginx_dir + "/sites-enabled/iqrf-daemon-webapp.localhost"
	send_command("cp -u nginx/" + virtualhost + " " + available_virtualhost)
	if not os.path.lexists(enabled_virtualhost): 
		send_command("ln -s " + available_virtualhost + " " + enabled_virtualhost)

def fix_php_fpm_config(config_file):
	send_command("sed 's/;cgi\\.fix_pathinfo=1/cgi\\.fix_pathinfo=0/g' " + config_file + " > " + config_file)

def enable_sudo(sudoers_file=SUDOERS_FILE):
	found = False
	sudoers = "www-data ALL=(ALL) NOPASSWD:ALL"
	with fileinput.FileInput(sudoers_file) as file:
		for line in file:
			if line.strip() == sudoers:
				found = True
	if not found:
		send_command("echo \"" + sudoers + "\" >> " + sudoers_file)
	
def restart_service(name):
	send_command("systemctl restart " + name)

if __name__ == "__main__":
	main()
