Feature: Current user

  @api
  Scenario: Log in with correct username and password
	Given I am an unauthenticated user
	When I create HTTP "POST" request to "/user/signIn" with JSON object body:
	  | username | password |
	  | admin    | iqrf     |
	Then HTTP status code is 200

  @api
  Scenario: Log in with incorrect password
	Given I am an unauthenticated user
	When I create HTTP "POST" request to "/user/signIn" with JSON object body:
	  | username | password |
	  | admin    | pass     |
	Then HTTP status code is 400

  @api
  Scenario: Log in with incorrect username and password
	Given I am an unauthenticated user
	When I create HTTP "POST" request to "/user/signIn" with JSON object body:
	  | username | password |
	  | unknown  | pass     |
	Then HTTP status code is 400

  @api
  Scenario: Get information about unauthorized user
	Given I am an unauthenticated user
	When I create HTTP "GET" request to "/user" without body
	Then HTTP status code is 401

  @api
  Scenario: Get information about authorized user
	Given I log in as "admin" with password "iqrf"
	And I am an authenticated user
	When I create HTTP "GET" request to "/user" without body
	Then HTTP status code is 200
	And HTTP response contains JSON object:
	  | id | username | language | role  |
	  | 1  | admin    | en       | power |
