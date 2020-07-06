Feature: Optional feature manager

	@api
	Scenario: Get optional features configuration
		Given I log in as "admin" with password "iqrf"
		And I am an authenticated user
		When I create HTTP "GET" request to "/features" without body
		Then HTTP status code is 200
		And HTTP response contains '{"docs":{"enabled":true,"url":"https://docs.iqrf.org/iqrf-gateway/"},"grafana":{"enabled":false,"url":"/grafana/"},"networkManager":{"enabled":false},"nodeRed":{"enabled":false,"url":"/node-red/"},"pixla":{"enabled":false},"ssh":{"enabled":false},"supervisord":{"enabled":false,"url":"/supervisord/"},"trUpload":{"enabled":false},"updater":{"enabled":false},"unattendedUpgrades":{"enabled":false},"versionChecker":{"enabled":false}}'

	@api
	Scenario: Get optional feature configuration
		Given I log in as "admin" with password "iqrf"
		And I am an authenticated user
		Then Optional feature "grafana" has configuration:
		  | enabled | url       |
		  | false   | /grafana/ |

	@api
	Scenario: Get optional nonexistent feature configuration
		Given I log in as "admin" with password "iqrf"
		And I am an authenticated user
		When I create HTTP "GET" request to "/features/nonsense" without body
		Then HTTP status code is 404

	@api
	Scenario: Edit optional feature configuration
		Given I log in as "admin" with password "iqrf"
		And I am an authenticated user
		When I edit optional feature "grafana":
			| enabled | url         |
			| true    | /dashboard/ |
		Then HTTP status code is 200
		And Optional feature "grafana" has configuration:
			| enabled | url         |
			| true    | /dashboard/ |

	@api
	Scenario: Edit optional feature configuration with missing parameter
		Given I log in as "admin" with password "iqrf"
		And I am an authenticated user
		When I edit optional feature "grafana":
			| enabled |
			| false   |
		Then HTTP status code is 400
		And Optional feature "grafana" has configuration:
			| enabled | url         |
			| true    | /dashboard/ |

	@api
	Scenario: Edit optional feature configuration with extra parameter
		Given I log in as "admin" with password "iqrf"
		And I am an authenticated user
		When I edit optional feature "grafana":
			| enabled | url         | extra |
			| false   | /dashboard/ | 12334 |
		Then HTTP status code is 400
		And Optional feature "grafana" has configuration:
			| enabled | url         |
			| true    | /dashboard/ |

	@api
	Scenario: Edit nonexistent optional feature configuration
		Given I log in as "admin" with password "iqrf"
		And I am an authenticated user
		When I edit optional feature "nonsense":
			| enabled |
			| true    |
		Then HTTP status code is 404
