# How to install IQRF Gateway Webapp

## Add PHP 7.2 repository

If you are using Debian 9, UbiLinux 4 or Ubuntu 16.04 you have had to add PHP 7.2 repository.

### For Debian
```
sudo apt-get -y install apt-transport-https lsb-release ca-certificates
sudo wget -O /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg
sudo sh -c 'echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" > /etc/apt/sources.list.d/php.list'
sudo apt-get update
```

### For UbiLinux
```
sudo apt-get -y install apt-transport-https lsb-release ca-certificates
sudo wget -O /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg
sudo sh -c 'echo "deb https://packages.sury.org/php/ stretch main" > /etc/apt/sources.list.d/php.list'
sudo apt-get update
```

### For Ubuntu
```
sudo add-apt-repository ppa:ondrej/php
sudo apt-get update
```

## Add IQRF Gateway repository

### For Debian and UbiLinux
```
sudo apt-get install dirmngr
sudo apt-key adv --keyserver keyserver.ubuntu.com --recv-keys 9C076FCC7AB8F2E43C2AB0E73241B9B7B4BD8F8E
echo "deb https://repos.iqrfsdk.org/testing/debian stretch testing" | sudo tee -a /etc/apt/sources.list
sudo apt-get update
```

### For Ubuntu
Currently Ubuntu is not supported.

## [Install IQRF Gateway Daemon](https://github.com/iqrfsdk/iqrf-gateway-daemon/blob/master/INSTALL.md)

## Install IQRF Gateway webapp
```
sudo apt-get install iqrf-gateway-webapp
```
