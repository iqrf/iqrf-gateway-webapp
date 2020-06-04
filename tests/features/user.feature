Feature: Current user

  @api
  Scenario: Get information about unauthorized user
	Given I am an unauthenticated user
	When I create HTTP "GET" request to "/user" without body
	Then HTTP status code is "401"

  @api
  Scenario: Get information about authorized user
	Given I log in as "admin" with password "iqrf"
	And I am an authenticated user
	When I create HTTP "GET" request to "/user" without body
	Then HTTP status code is "200"
	And HTTP response contains '{"id":1,"username":"admin","language":"en","role":"power"}'
