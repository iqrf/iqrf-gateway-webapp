Feature: User manager

  @api
  Scenario: List all users before adding
	Given I log in as "admin" with password "iqrf"
	And I am an authenticated user
	When I create HTTP "GET" request to "/users" without body
	Then HTTP status code is 200
	And HTTP response contains JSON array of objects:
	  | id | username | role  | language |
	  | 1  | admin    | power | en       |

  @api
  Scenario: Get information about an existing user
	Given I log in as "admin" with password "iqrf"
	And I am an authenticated user
	When I create HTTP "GET" request to "/users/1" without body
	Then HTTP status code is 200
	And HTTP response contains JSON object:
	  | id | username | role  | language |
	  | 1  | admin    | power | en       |

  @api
  Scenario: Get information about a nonexistent user
	Given I log in as "admin" with password "iqrf"
	And I am an authenticated user
	When I create HTTP "GET" request to "/users/2" without body
	Then HTTP status code is 404

  @api
  Scenario: Create a new user
	Given I log in as "admin" with password "iqrf"
	And I am an authenticated user
	When I create HTTP "POST" request to "/users" with JSON object body:
	  | username | password | language | role   |
	  | user     | iqrf     | en       | normal |
	Then HTTP status code is 201

  @api
  Scenario: Create a new user without username
	Given I log in as "admin" with password "iqrf"
	And I am an authenticated user
	When I create HTTP "POST" request to "/users" with JSON object body:
	  | password | language | role   |
	  | iqrf     | en       | normal |
	Then HTTP status code is 400

  @api
  Scenario: Create a new user without password
	Given I log in as "admin" with password "iqrf"
	And I am an authenticated user
	When I create HTTP "POST" request to "/users" with JSON object body:
	  | username | language | role   |
	  | user     | en       | normal |
	Then HTTP status code is 400

  @api
  Scenario: Create a new user without role
	Given I log in as "admin" with password "iqrf"
	And I am an authenticated user
	When I create HTTP "POST" request to "/users" with JSON object body:
	  | username | password | language |
	  | user     | iqrf     | en       |
	Then HTTP status code is 400

  @api
  Scenario: Create a new user without language
	Given I log in as "admin" with password "iqrf"
	And I am an authenticated user
	When I create HTTP "POST" request to "/users" with JSON object body:
	  | username | password | role   |
	  | user     | iqrf     | normal |
	Then HTTP status code is 400

  @api
  Scenario: Create a new user with username collision
	Given I log in as "admin" with password "iqrf"
	And I am an authenticated user
	When I create HTTP "POST" request to "/users" with JSON object body:
	  | username | password | language | role   |
	  | user     | iqrf     | en       | normal |
	Then HTTP status code is 400

  @api
  Scenario: List all users after adding
	Given I log in as "admin" with password "iqrf"
	And I am an authenticated user
	When I create HTTP "GET" request to "/users" without body
	Then HTTP status code is 200
	And HTTP response contains JSON array of objects:
	  | id | username | role   | language |
	  | 1  | admin    | power  | en       |
	  | 2  | user     | normal | en       |

  @api
  Scenario: Change username without collision
	Given I log in as "admin" with password "iqrf"
	And I am an authenticated user
	When I create HTTP "PUT" request to "/users/2" with JSON object body:
	  | username |
	  | iqrf     |
	Then HTTP status code is 200

  @api
  Scenario: Change username
	Given I log in as "admin" with password "iqrf"
	And I am an authenticated user
	When I create HTTP "PUT" request to "/users/2" with JSON object body:
	  | username |
	  | iqrf     |
	Then HTTP status code is 200

  @api
  Scenario: Change username with collision
	Given I log in as "admin" with password "iqrf"
	And I am an authenticated user
	When I create HTTP "PUT" request to "/users/2" with JSON object body:
	  | username |
	  | admin    |
	Then HTTP status code is 400

  @api
  Scenario: Change nonexistent user
	Given I log in as "admin" with password "iqrf"
	And I am an authenticated user
	When I create HTTP "PUT" request to "/users/3" with JSON object body:
	  | username |
	  | iqrf     |
	Then HTTP status code is 404

  @api
  Scenario: List all users after username change
	Given I log in as "admin" with password "iqrf"
	And I am an authenticated user
	When I create HTTP "GET" request to "/users" without body
	Then HTTP status code is 200
	And HTTP response contains JSON array of objects:
	  | id | username | role   | language |
	  | 1  | admin    | power  | en       |
	  | 2  | iqrf     | normal | en       |

  @api
  Scenario: Delete an existing user
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
	And HTTP response contains JSON array of objects:
	  | id | username | role  | language |
	  | 1  | admin    | power | en       |

  @api
  Scenario: Delete an nonexistent user
	Given I log in as "admin" with password "iqrf"
	And I am an authenticated user
	When I create HTTP "DELETE" request to "/users/2" without body
	Then HTTP status code is 404
