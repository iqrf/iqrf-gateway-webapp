Feature: Service manager

  @api
  Scenario: Get supported services
	Given I log in as "admin" with password "iqrf"
	And I am an authenticated user
	When I create HTTP "GET" request to "/services" without body
	Then HTTP status code is 200
	And HTTP response contains '{"services":["iqrf-gateway-daemon","unattended-upgrades","ssh"]}'
