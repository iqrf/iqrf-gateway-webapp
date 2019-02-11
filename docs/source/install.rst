**********************************
How to install IQRF Gateway Webapp
**********************************

Add PHP 7.2 repository
######################

If you are using Debian 9, Raspbian 9, UbiLinux 4 or Ubuntu 16.04 you have to add PHP 7.2 repository.

For Debian
----------
.. code-block:: bash

	sudo apt-get -y install apt-transport-https lsb-release ca-certificates
	sudo wget -O /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg
	sudo sh -c 'echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" > /etc/apt/sources.list.d/php.list'
	sudo apt-get update


For Raspbian
------------
.. code-block:: bash

	sudo apt-get -y install apt-transport-https lsb-release ca-certificates dirmngr
	sudo apt-key adv --keyserver keyserver.ubuntu.com --recv-keys D93B0C12C8D04D7AAFBCFA27CCD91D6111A06851
	sudo sh -c 'echo "deb https://repozytorium.mati75.eu/raspbian stretch-backports main contrib non-free" > /etc/apt/sources.list.d/php.list'
	sudo apt-get update

For UbiLinux
------------
.. code-block:: bash

	sudo apt-get -y install apt-transport-https lsb-release ca-certificates
	sudo wget -O /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg
	sudo sh -c 'echo "deb https://packages.sury.org/php/ stretch main" > /etc/apt/sources.list.d/php.list'
	sudo apt-get update


For Ubuntu
----------
.. code-block:: bash

	sudo add-apt-repository ppa:ondrej/php
	sudo apt-get update


Add IQRF Gateway repository
###########################

For Debian and UbiLinux
-----------------------
.. code-block:: bash

	sudo apt-get install dirmngr apt-transport-https
	sudo apt-key adv --keyserver keyserver.ubuntu.com --recv-keys 9C076FCC7AB8F2E43C2AB0E73241B9B7B4BD8F8E
	echo "deb https://repos.iqrf.org/testing/debian stretch testing" | sudo tee -a /etc/apt/sources.list
	sudo apt-get update

For Ubuntu
----------

Xenial 16.04
++++++++++++
.. code-block:: bash

	sudo apt-get install dirmngr apt-transport-https
	sudo apt-key adv --keyserver keyserver.ubuntu.com --recv-keys 9C076FCC7AB8F2E43C2AB0E73241B9B7B4BD8F8E
	echo "deb https://repos.iqrf.org/testing/ubuntu/xenial xenial testing" | sudo tee -a /etc/apt/sources.list
	sudo apt-get update

Bionic 18.04
++++++++++++
.. code-block:: bash

	sudo apt-get install dirmngr apt-transport-https
	sudo apt-key adv --keyserver keyserver.ubuntu.com --recv-keys 9C076FCC7AB8F2E43C2AB0E73241B9B7B4BD8F8E
	echo "deb https://repos.iqrf.org/testing/ubuntu/bionic bionic testing" | sudo tee -a /etc/apt/sources.list
	sudo apt-get update

Install IQRF Gateway Daemon
###########################
Follow the `IQRF Gateway Daemon's installation guide <https://docs.iqrf.org/iqrf-gateway-daemon/install.html>`_.

Install IQRF Gateway webapp
###########################
.. code-block:: bash

	sudo apt-get install iqrf-gateway-webapp
