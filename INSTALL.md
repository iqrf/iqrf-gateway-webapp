# Installation

## Installation with web server

### Nginx (automatic instalation)

If you use Debian 8 (jessie), Debian 9 (stretch) or Ubuntu 16.04 (xenial), you can use the installer which is located in install directory of this project:

**Debian 8 (jessie)**
```bash
git clone https://github.com/iqrfsdk/iqrf-gateway-webapp.git
cd iqrf-gateway-webapp/install/
python3 install.py -d debian -v 8
```

**Debian 9 (stretch)**
```bash
git clone https://github.com/iqrfsdk/iqrf-gateway-webapp.git
cd iqrf-gateway-webapp/install/
python3 install.py -d debian -v 9
```

**Ubuntu 16.04 (xenial)**
```bash
git clone https://github.com/iqrfsdk/iqrf-gateway-webapp.git
cd iqrf-gateway-webapp/install/
sudo python3 install.py -d ubuntu -v 16.04
```

Then visit `http://localhost` in your browser to see the welcome page.

### Development built-in PHP server

The simplest way to get started is to start the built-in PHP server in the root directory of this project:

```bash
git clone https://github.com/iqrfsdk/iqrf-gateway-webapp.git
cd iqrf-gateway-webapp/
php -S localhost:8000 -t www/
```

Then visit `http://localhost:8000` in your browser to see the welcome page.

## Instalation without web server

The best way to install this project is using Composer. If you don't have Composer yet, download it following [the instructions](https://doc.nette.org/composer). Then use command:

### Development version

```bash
git clone https://github.com/iqrfsdk/iqrf-gateway-webapp.git
cd iqrf-gateway-webapp/
composer install
```

Make directories `temp/` and `log/` writable.

### Stable version

```bash
composer create-project iqrfsdk/iqrf-gateway-webapp
```
