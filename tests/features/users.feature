Feature: User manager

  @api
  Scenario: List all users before adding
	Given I log in as "admin" with password "iqrf"
	And I am an authenticated user
	When I create HTTP "GET" request to "/users" without body
	Then HTTP status code is 200
	And HTTP response contains '[{"id":1,"username":"admin","role":"power","language":"en"}]'

  @api
  Scenario: Create a new user
	Given I log in as "admin" with password "iqrf"
	And I am an authenticated user
	When I create HTTP "POST" request to "/users" with body '{"username":"user","password":"iqrf","language":"en","role":"normal"}'
	Then HTTP status code is 201

  @api
  Scenario: List all users after adding
	Given I log in as "admin" with password "iqrf"
	And I am an authenticated user
	When I create HTTP "GET" request to "/users" without body
	Then HTTP status code is 200
	And HTTP response contains '[{"id":1,"username":"admin","role":"power","language":"en"},{"id":2,"username":"user","role":"normal","language":"en"}]'

  @api
  Scenario: Change username
	Given I log in as "admin" with password "iqrf"
	And I am an authenticated user
	When I create HTTP "PUT" request to "/users/2" with body '{"username":"iqrf"}'
	Then HTTP status code is 200

  @api
  Scenario: List all users after username change
	Given I log in as "admin" with password "iqrf"
	And I am an authenticated user
	When I create HTTP "GET" request to "/users" without body
	Then HTTP status code is 200
	And HTTP response contains '[{"id":1,"username":"admin","role":"power","language":"en"},{"id":2,"username":"iqrf","role":"normal","language":"en"}]'

  @api
  Scenario: Delete a user
	Given I log in as "admin" with password "iqrf"
	And I am an authenticated user
	When I create HTTP "DELETE" request to "/users/2" without body
	Then HTTP status code is 200

  @api
  Scenario: List all users after deletion
	Given I log in as "admin" with password "iqrf"
	And I am an authenticated user
	When I create HTTP "GET" request to "/users" without body
	Then HTTP status code is 200
	And HTTP response contains '[{"id":1,"username":"admin","role":"power","language":"en"}]'


