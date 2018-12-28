# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/).

## [Unreleased]
### Added:
- Add a skeleton of user documentation
- Add the API documentation and User documentation deployment to GitLab CI
- Add MQTT topics to the MQTT datagrid
- Add a method to get UART interfaces available in the system
- Add the IQRF UART interface configuration tool
- Add JSON highlighter
- Add debug information into WebSocket client
- Add a guide how to install PHP 7.2 on Raspbian 9
- Create the directory for certificates for MQTT connections
- Add a SPI port mapping
- Add a port and pins mapping for UART interface
- Allow status changes from the datagrid for MQ, MQTT and WebSocket interface
- Allow status changes from the datagrid for WebSocket messaging
- Add links to PDF and video guides for cloud services
- Add the CLI tool for managing webapp
- Add man page
- Add scheduler's configuration migration
- Add disk, memory and swap usages
- Add IQMESH Network bonding manager
- Add IQMESH enumeration manager, add DPA version and RF mode to GW info
- Add information about IQRF Gateway to GW info
- Add Docker testing images building and deploying into GitLab CI
- Add a trace file configuration tool
- Add SPI restart option into IQRF SPI configuration tool
- Set IQRF Gateway Daemon's WS server URL via ENV variable

### Changed:
- Update IQRF Gateway Daemon's SPI configuration tool
- Update the installation guide
- Hide OTA upload configuration tool
- Show only necessary components for a normal user
- Update names of IQRF Gateway Daemon's directories
- Update SPI and UART GPIO pins names
- Rename the configuration tool for components for normal users
- Move the navigation to own template
- Update the PGP key of PHP repository for Raspbian
- Update the root CA certificate for Amazon AWS IoT
- Cleanup IQRF DPA configuration tool
- Change namespace for IQRF Network module
- Scheduler uses APIv2 and displays the task time in human readable format
- Improve user's data grid
- Remove scheduler from manageable components by normal user
- Update the installation guide
- Decrease default WS client timeout to 26 seconds
- Use new API for setting an access password and an user key
- Change the order of cloud services
- Update notification about a new version and about an offline mode
- Update IQRF IDE Macros
- Update Sentry's DSN
- Build new Docker images for each commit

### Removed:
- Remove support of the old WebSocket service - shape::WebsocketService

### Fixed:
- Catch exceptions in the scheduler configuration manager
- Fix SPI pins mapping tool for SBCs
- Fix permissions in the Debian package
- Fix typo in IQMESH configuration tool's presenter
- Fix lintian tag `embedded-javascript-library`
- Fix a grammatical mistake in the user documentation
- Fix components order in the generic configuration manager
- Fix a bug in the IQRF Gateway Daemon's configuration import
- Fix a translation in GW info
- Fix bug in a saving of IQRF Gateway Daemon's configuration file
- Fix configuration error messages
- Fix the path for certificates for MQTT connections
- Fix bug in the generic cloud service manager
- Fix component's status changing from datagrid
- Fix redrawing of the component's datagrid
- Fix websocket interface manager
- Fix bugs in JSON validation against the JSON schema
- Fix typos, update PHPDocs, sort imports and format source code
- Fix send DPA raw form
- Fix bugs in the scheduler's configuration tool
- Fix IQRF Gateway Daemon's log viewer
- Fix timezone in tests
- Fix URL for checking updates
- Fix bug in GW diagnostics
- Fix name of IBM Cloud
- Fix disk usage unit conversion
- Fix bug in a swap usage
- Fix changing of the IQRF Gateway Daemon mode
- Reload nginx service only if nginx service is started in Debian package installation and uninstallation
- Fix Docker testing images building and deploying in GitLab CI

## [2.0.0-beta] - 2018-09-05
### Added:
- Add configuration tools for IQRF Gateway Daemon v2
- Add PHPSTan - tool for static analysis
- Add JSON schema validation
- Add support for Debian testing and Ubuntu 18.04 in the installer
- Add an user manager
- Add the installation wizard

### Changed:
- Hide JSON Raw API and JSON Splitter configuration to a normal user
- Move the link for the IQRF Gateway Daemon's configuration migration under the Gateway module
- Drop PHP 7.0 support
- Move core functionality into own module (CoreModule)
- Update dependencies
- Replace `iqrfapp` with a WebSocket client
- Rename project to `iqrf-gateway-webapp`

### Removed:
- Removed configuration tools for IQRF Gateway Daemon v1

### Fixed:
- Fix return type hints, class imports, call parent's constructor in constructors, etc.
- Catch all exceptions and show error messages
- Fix bugs in the ZIP archive manager

## [1.1.6] - 2018-07-10
### Added:
- Add a notification to an update webapp to newer stable version

### Changed:
- Modify an installer for an installation of different versions of webapp from git branches

### Fixed:
- Fix a parsing of broadcast DPA packets

## [1.1.5] - 2018-07-02
### Changed:
- Updated IQRF IDE4 macros

## [1.1.4] - 2018-06-14
### Added:
- Add a list of default SPI mappings for Raspberry Pi, Orange Pi and UP board into IQRF interface manager
- Add Dockerfile for testing webapp in GitLab CI
- Add configuration migration
- Add a composer's package caching for GitLab CI

### Changed:
- Change a displaying of a webapp's version
- Update messages of Service manager (restart, not supported init system)
- Load CSS and JS from localhost
- Use configurable paths to IQRF Gateway Daemon's configuration and log
- Set PHP 7.0 as default version of PHP in testing Docker image
- Disable CSRF protection for Sign in form

### Removed:
- Delete the configuration directory via a command in the configuration import method

### Fixed:
- Fix bug in the installer
- Catch an exception when IQRF Gateway Daemon does not send response
- Fix TR info on a page GW Info
- Fix board name showing for boards with device tree support
- Delete the configuration directory via a command in the configuration import method
- Fix an ownership for the configuration directory in a configuration upload

## [1.1.3] - 2018-06-05
### Added:
- Add an information about gateway's board into the GW info and in a diagnostics data
- Add more tests for the GW Info model
- Add information about connected SPI and USB devices into diagnostics
- Add information about services into diagnostics
- Add IQRF Gateway Daemon's configuration and IQRF Gateway Daemon Webapp's logs into diagnostics

### Removed:
- Remove an unnecessary word 'version' in a Gateway info template

### Fixed:
- Add an installation of ZIP extension for PHP in the webapp's installer
- Complete a migration to a newer package for translations (fix #38)
- Fix typo in a creation of a new MQTT interface into IBM Bluemix

## [1.1.2] - 2018-05-10
### Added:
- Add more tests for a Base service manager
- Add more tests for an Instance manager
- Add more tests for an IQRF App module
- Add a test for a check a certificate and a private key in AWS cloud manager
- Add a test for an uploading a certificate and a private key in AWS cloud manager
- Add a test for an generation of an Azure IoT Hub's SAS token (fix #26)

### Fixed:
- Fix a bug in an addition of a new Base service or a MQ/MQTT instance
- Fix a bug in a DPA packet parsing

## [1.1.1] - 2018-05-07
### Added:
- Add a response parsing of a DPA request "read HWP configuration"
- Add a button for a creating a new MQTT interface for cloud services and a restarting the IQRF Gateway daemon
- Add methods for deleting Base services and MQ/MQTT interfaces by their name
- Add a Base service deletion in a MQ/MQTT interface deletion process
- Add a showing of the webapp's version on the page 'GW Info'
- Add a downloading of a basic diagnostic data

### Changed:
- Optimise parser for RF Band from HWP configuration

### Removed:
- Remove an extra page in the MQTT configuration tools for cloud services

### Fixed:
- Fix a certificate validation in a creation a new connection into Amazon AWS IoT
- Fix bugs in a creation of a new Base service in the configuration tool
- Remove a duplicated Base service and MQTT interface in a creation of a MQTT connection into cloud services
- Fix typo in a creation form of a new MQTT connection into IBM Bluemix

## [1.1.0] - 2018-03-28
### Added:
- Add more (DC)TR types
- Add unit for RSSI in DPA OS read response
- Add select box for selecting Base service into configuration tool of scheduler
- Add select box for selecting messaging into configuration tool of Base services
- Add TLS connection for the MQTT connection into IBM Bluemix Cloud
- Add option to overwrite NADR in Send raw DPA packet form (fix #10)
- Add downloading CA certificate for Amazon AWS IoT in adding new MQTT connection to this cloud
- Add parser for DPA Enumeration response
- Show an error message if the entered DPA packet is invalid on the page Send DPA packet
- Add validation of entered private key and certificate in form for adding new MQTT connection to Amazon AWS IoT
- Add setting the Access password and the User Key into the IQMESH Network manager
- Add methods for reading the HWP configuration and for writing byte into the HWP configuration
- Add changing of RF channels for coordinator, refactor IQMESH Network Manager
- Add methods for setting of LP timeout, RF output power and RF signal filter
- Add definition of SPI pins for IQRF SPI interface
- Add a basic parser of the HWP configuration

### Changed:
- Update project's name
- Use scalar type declarations and return type declarations
- Move form's callbacks into new methods
- Install and use PHP 7.0 on Debian 8
- Use `Kdyby/Monolog` instead of `salamek/raven-nette` for logging into Sentry
- Use @inject annotations for injecting form factories into presenters
- Update DSN for Sentry

### Removed:
- Remove PHP 5.6 support
- Remove default value for port in form for adding MQTT connection into Inteliments InteliGlue

### Fixed:
- Fix parsing of the IQRF OS build from the DPA OS read request
- Fix parameter type of method `handleShowResponse` in presenter for sending raw DPA requests
- Fix DPA packet on the server-side
- Make DPA response parsers case-insensitive
- Fix showing Embedded peripherals in the parsed response on Send DPA packet page
- Fix HWPID format on the scheduler's dashboard
- Fix RF Band detection if webapp cannot connect to the coordinator
- Fix bugs in a creation of a new task in the Scheduler’s configuration tool
- Fix an issue with a DPA packet selection in IE 11 on the page Send DPA packet

## [1.0.0] - 2017-12-11
### Added:
- Parse last RSSI from DPA OS read response
- Add enhanced command manager
- Add wizard for adding new MQTT instance from IBM Bluemix
- Add button to clear all bonds into IQMESH Network manager
- Add showing of debugging data about iqrfapp in Tracy's debug bar
- Add Sentry for logging exceptions (fix #36)
- Add method for cleaning all bonds into IQMESH manager
- Implement new way to get version of installed iqrf-daemon
- Add selection of verbosity level of iqrfapp
- Add dashboard for IQRF Gateway Docker image
- Add IQMESH Discovery to IQMESH Network Manager
- Add adding new base service when new MQTT interface is added via module 'Clouds'
- Add methods for adding new Base services and Instances
- Add default login credentials to Read me
- Add MQTT configuration for Amazon AWS IoT cloud
- Add MQTT configuration for Inteliments InteliGlue cloud
- Add composer's script for checking/fixing coding standards
- Add ApiGen for a generating documentation
- Add address's validation to IQMESH Network Manager
- Add rebond node and remove node to IQMESH Network Manager
- Add skeleton of IQMESH Network Manager - bonding new nodes
- Add new Service manager - supervisor for Docker containers
- Add Dockerfiles for Raspberry Pi (models 2B and 3B) and amd64

### Changed:
- Merge forms for IQMESH Network bonding into one
- Change default TX Power for IQMESH Discovery to 6
- Update label for Azure IoT Connection String for Device, add PHPDocs in form
- Update dependencies 'nette/php-generator' and 'mockery/mockery'
- Redesign IQMESH Network Manager
- Split configuration into modules
- Update Debian to stretch in Dockerfiles for Raspberry Pi
- Modify texts in the Dashboard
- Rename files with unit tests
- Rename JSON DPA property 'req_data' to 'rdata'

### Deleted:
- Remove unused private field '$fileName' in AzurePresenter

### Fixed:
- Fix bug in installer with sed command
- Fix issue with the undefined offset in JSON array when new base service is added
- Fix issue with the undefined offset in JSON array when new instance is added
- Fix issue with empty response from iqrfapp
- Fix problem with iqrfapp in DBG mode or with some other messages
- Fix typo in 'iqrfapp readonly' command and fix tests for iqrfapp
- Workaround to fix mismatched msgid in 'iqrfapp'
- Fix bug about timeout = 0
- Make directories 'log' and 'temp' writable
- Catch exception 'Nette\IOException' in forms for adding new MQTT interface
- Catch and throw an exception if the MS Azure IoT Hub connection string for device is invalid
- Fix bug in the IQMESH Discovery command
- Catch exceptions when iqrf-daemon's log not found
- Fix URLs in Inteliments InteliGlue manager
- Attempt to fix bug in AWS IoT manager
- Fix bad index for new Base services, MQ instances and MQTT instances
- Fix bug in the installer - remove webapp's directory when it's upgraded from stable to development version
- Fix redirect in 'CloudAzureMqttFormFactory' form
- Fix path to templates for error pages
- Catch and throw 'NotSupportedInitSystemException' exception if the used init system is not supported
- Throw and catch exception if JSON DPA response is empty
- Fix the path to root of the webapp
- Refactor the JavaScript script

## [0.8.1] - 2017-10-03
### Added:
- Add adding and deleting new MQ interfaces
- Add section 'Clouds' for managing cloud services (e.g. Microsoft Azure IoT Hub MQTT)

### Changed:
- Rename 'IQRF Daemon' to 'IQRF Gateway' in the layout template

### Deleted:
- Delete Czech translation

### Fixed:
- Fix deleting MQ and MQTT instances
- Fix path to page "Error 500 - Server Error"

## [0.8.0] - 2017-09-29
### Added:
- Add arguments for selecting stability (`dev`/`stable`) of this project in installer
- Add more parameters for configuration of main daemon settings - file config.json (fix #17)
- Add configuration parameter CommunicationMode STD/LP (IqrfInterface.json)
- Add parameter `DefaultTimeout` to configurator of iqrfapp
- Add properties `AsyncDpaMessage` in the Base service configurator
- Add showing Async messaging status in Base service dashboard
- Add interfaces name to list of IPv4 and IPv6 addresses and MAC addresses (fix #7)
- Add adding new tasks to Scheduler (fix #13) removing Scheduler tasks
- Add adding new MQTT interfaces and removing MQTT interfaces
- Add adding new MQTT interface via the MS Azure IoT Hub connection string
- Add adding new base services and removing base services
- Add list of available interfaces in configuration of IQRF interface
- Add changing gateway mode
- Add parser for Coordinator DPA responses for command "Get bonded nodes" and for command "Get discovered nodes"
- Add parser for OS DPA responses for command "READ"
- Add showing NADR and DPA packet (only for types raw and raw-hdp) in Scheduler configuration tool dashboard
- Add checkbox for enabling user's defined DPA timeout in iqrfapp
- Add basic Docker image for the webapp
- Add viewer of the IQRF Daemon's log
- Add showing version of the IQRF Daemon in the GW info
- Add showing DPA JSON request and response
- Add Content Security Policy rules

### Changed:
- Swap serializers in configuration of Base services
- Redesign GW info page (fix #22)
- Use POSIX timestamp as `msgid` in the JSON DPA request (fix #21)
- Send IQRF DPA raw packets in JSON via iqrfapp
- Split this project into modules
- Unify colors of buttons, add signpost for Gateway module
- Improve DPA timeout settings in IQRF Net module (fix #24)
- Rename "IQRF App" to "IQRF Net"
- Move "Change GW mode" into Gateway module
- Use dropdown buttons for macros form IQRF IDE.
- Move IQRF App - send raw packet page (fix #9)


### Fixed:
- Fix order of configuration pages in navigation (fix #11)
- Fix iqrfapp parser for working with new version of iqrfapp
- Fix redirect after saving Tracer settings
- Fix bug in installer (updating dependencies)


## [0.5.0] - 2017-07-12
### Added
- Add configuration tools for IQRF Gateway Daemon
- Add IQRF Gateway daemon's service manager
- Add sending of raw DPA packets
- Add a basic information about the gateway
- Add a basic installation tool
- Add IQRF IDE4 macros parser

[Unreleased]: https://gitlab.iqrf.org/open-source/iqrf-gateway-webapp/compare/v2.0.0-beta...master
[2.0.0-beta]: https://gitlab.iqrf.org/open-source/iqrf-gateway-webapp/compare/v1.1.6...v2.0.0-beta
[1.1.6]: https://gitlab.iqrf.org/open-source/iqrf-gateway-webapp/compare/v1.1.5...v1.1.6
[1.1.5]: https://gitlab.iqrf.org/open-source/iqrf-gateway-webapp/compare/v1.1.4...v1.1.5
[1.1.4]: https://gitlab.iqrf.org/open-source/iqrf-gateway-webapp/compare/v1.1.3...v1.1.4
[1.1.3]: https://gitlab.iqrf.org/open-source/iqrf-gateway-webapp/compare/v1.1.2...v1.1.3
[1.1.2]: https://gitlab.iqrf.org/open-source/iqrf-gateway-webapp/compare/v1.1.1...v1.1.2
[1.1.1]: https://gitlab.iqrf.org/open-source/iqrf-gateway-webapp/compare/v1.1.0...v1.1.1
[1.1.0]: https://gitlab.iqrf.org/open-source/iqrf-gateway-webapp/compare/v1.0.0...v1.1.0
[1.0.0]: https://gitlab.iqrf.org/open-source/iqrf-gateway-webapp/compare/v0.8.1...v1.0.0
[0.8.1]: https://gitlab.iqrf.org/open-source/iqrf-gateway-webapp/compare/v0.8.0...v0.8.1
[0.8.0]: https://gitlab.iqrf.org/open-source/iqrf-gateway-webapp/compare/v0.5.0...v0.8.0
[0.5.0]: https://gitlab.iqrf.org/open-source/iqrf-gateway-webapp/compare/35daaafb...v0.5.0
