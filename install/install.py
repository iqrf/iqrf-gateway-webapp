#!/usr/bin/python3
# -*- coding: utf-8 -*-

"""
Copyright 2017 MICRORISC s.r.o.
Copyright 2017 IQRF Tech s.r.o.

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
"""

import argparse
import fileinput
import os
import subprocess

ARGS = argparse.ArgumentParser(description="IQRF daemon webapp installer.")
ARGS.add_argument("-d", "--dist", action="store", dest="dist", required=True, type=str, help="The used linux distribution.")
ARGS.add_argument("-v", "--ver", action="store", dest="ver", required=True, type=str, help="The version of used linux distribution.")
ARGS.add_argument("-s", "--stability", action="store", dest="stability", default="stable", type=str, help="The stability of the iqrf-daemon-webapp.")

WEBSERVER_DIRECTORY = "/var/www"
WEBAPP_DIRECTORY = WEBSERVER_DIRECTORY + "/iqrf-daemon-webapp"
SUDOERS_FILE = "/etc/sudoers"

def send_command(cmd):
	"""
	Execute shell command and return output
	@param cmd Command to exec
	@return string Output
	"""
	print(cmd)
	return subprocess.Popen(cmd, shell=True, stdout=subprocess.PIPE, stderr=subprocess.PIPE).stdout.read()

def main():
	"""
	Main program function
	"""
	args = ARGS.parse_args()
	dist = args.dist.lower()
	ver = args.ver.lower()
	stability = args.stability.lower()

	if dist == "debian" or dist == "raspbian":
		install_debian(ver, stability)
	elif dist == "ubuntu":
		install_ubuntu(ver, stability)

def install_debian(version, stability="stable"):
	"""
	Install iqrf-daemon-webapp on Debian
	@param version Version of Debain
	"""
	send_command("apt-get update")
	if version == "8" or version == "jessie" or version == "oldstable":
		# Install sudo, nginx and php5
		send_command("apt-get install -y sudo php5 php5-common php5-fpm php5-curl php5-json php5-sqlite nginx-full")
		# Install composer
		send_command("bash ./install-composer.sh")
		send_command("mv composer.phar /usr/bin/composer")
		chmod_dir()
		if stability == "dev":
			install_php_app(WEBAPP_DIRECTORY, True)
		else:
			install_php_app(WEBAPP_DIRECTORY, False)
		disable_default_nginx_virtualhost()
		create_nginx_virtualhost("iqrf-daemon-webapp_php5.localhost")
		fix_php_fpm_config("/etc/php5/fpm/php.ini")
		chown_dir(WEBAPP_DIRECTORY)
		enable_sudo()
		restart_service("sudo")
		restart_service("php5-fpm")
		restart_service("nginx")
	elif version == "9" or version == "stretch" or version == "stable":
		# Install sudo, nginx php7.0, composer and zip
		send_command("apt-get install -y sudo php7.0 php7.0-common php7.0-fpm php7.0-curl php7.0-json php7.0-sqlite php7.0-mbstring composer nginx-full zip unzip")
		chmod_dir()
		if stability == "dev":
			install_php_app(WEBAPP_DIRECTORY, True)
		else:
			install_php_app(WEBAPP_DIRECTORY, False)
		disable_default_nginx_virtualhost()
		create_nginx_virtualhost("iqrf-daemon-webapp_php7-0.localhost")
		fix_php_fpm_config("/etc/php/7.0/fpm/php.ini")
		chown_dir(WEBAPP_DIRECTORY)
		enable_sudo()
		restart_service("sudo")
		restart_service("php7.0-fpm")
		restart_service("nginx")

def install_ubuntu(version, stability="stable"):
	"""
	Install iqrf-daemon-webapp on Ubuntu
	@param version Version of Ubuntu
	"""
	send_command("apt-get update")
	if version == "16.04" or version == "xenial" or version == "xerus":
		# Install sudo, nginx php7.0, composer and zip
		send_command("apt-get install -y sudo php7.0 php7.0-common php7.0-fpm php7.0-curl php7.0-json php7.0-sqlite php7.0-mbstring composer nginx-full zip unzip")
		chmod_dir()
		if stability == "dev":
			install_php_app(WEBAPP_DIRECTORY, True)
		else:
			install_php_app(WEBAPP_DIRECTORY, False)
		disable_default_nginx_virtualhost()
		create_nginx_virtualhost("iqrf-daemon-webapp_php7-0.localhost")
		fix_php_fpm_config("/etc/php/7.0/fpm/php.ini")
		chown_dir(WEBAPP_DIRECTORY)
		enable_sudo()
		restart_service("sudo")
		restart_service("php7.0-fpm")
		restart_service("nginx")

def install_php_app(directory, use_git=True):
	"""
	Install iqrf-daemon-webapp
	@param directory Directory to install iqrf-daemon-webapp
	@param use_git Download iqrf-daemon-webapp from git
	"""
	if not os.path.isdir(directory):
		if use_git:
			send_command("cd " + directory + "/../ ; git clone https://github.com/iqrfsdk/iqrf-daemon-webapp")
			send_command("cd " + directory + " ; composer install")
		else:
			send_command("cd " + directory + "/../ ; composer create-project iqrfsdk/iqrf-daemon-webapp")
	else:
		send_command("rm -rf " + directory + "/temp/cache")
		if use_git:
			send_command("cd " + directory + " ; git pull origin")
		send_command("cd " + directory + " ; composer update")


def chmod_dir(directory="/etc/iqrf-daemon"):
	"""
	Change mode of directory
	@param directory Directory
	"""
	send_command("chmod -R 666 " + directory)
	send_command("chmod 777 " + directory)

def chown_dir(directory, new_owner="www-data"):
	"""
	Change owner of directory
	@param directory Directory
	@param new_owner New owner of directory
	"""
	send_command("chown -R " + new_owner + ":" + new_owner + " " + directory)

def disable_default_nginx_virtualhost(nginx_dir="/etc/nginx"):
	"""
	Disable default nginx virtualhost
	@param nginx_dir Directory with configuration files for nginx
	"""
	virtualhost = nginx_dir + "/sites-enabled/default"
	if os.path.isfile(virtualhost):
		os.remove(virtualhost)

def create_nginx_virtualhost(virtualhost, nginx_dir="/etc/nginx"):
	"""
	Create nginx virtualhost
	@param virtualhost Path to file with virtualhost configuration
	@param nginx_dir Directory with configuration files for nginx
	"""
	virtualhost_name = "iqrf-daemon-webapp.localhost"
	available_virtualhost = nginx_dir + "/sites-available/" + virtualhost_name
	enabled_virtualhost = nginx_dir + "/sites-enabled/" + virtualhost_name
	send_command("cp -u nginx/" + virtualhost + " " + available_virtualhost)
	if not os.path.lexists(enabled_virtualhost):
		send_command("ln -s " + available_virtualhost + " " + enabled_virtualhost)

def fix_php_fpm_config(config_file):
	"""
	Fix PHP configuration
	@param config_file Path to PHP configuration file
	"""
	send_command("sed 's/;cgi\\.fix_pathinfo=1/cgi\\.fix_pathinfo=0/g' " + config_file + " > " + config_file)

def enable_sudo(sudoers_file=SUDOERS_FILE, user="www-data"):
	"""
	Enable sudo for specific user
	@param sudoers_file Path to sudoers file (usually /etc/sudoers)
	@param user User
	"""
	found = False
	sudoers = user + " ALL=(ALL) NOPASSWD:ALL"
	with fileinput.FileInput(sudoers_file) as file:
		for line in file:
			if line.strip() == sudoers:
				found = True
	if not found:
		send_command("echo \"" + sudoers + "\" >> " + sudoers_file)

def restart_service(name):
	"""
	Restart systemd service
	@param name Name of service to restart
	"""
	send_command("systemctl restart " + name + ".service")

if __name__ == "__main__":
	main()
