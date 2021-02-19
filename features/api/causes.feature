@api
Feature:
  In order to see causes
  As a non logged-in user
  I should be able to access API causes

  Background:
    Given the following fixtures are loaded:
      | LoadCauseData |

  Scenario: As a non logged-in user I can see first page of active causes
    Given I add "Accept" header equal to "application/json"
    When I send a "GET" request to "/api/causes"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    {
      "metadata": {
        "total_items": 5,
        "items_per_page": 2,
        "count": 2,
        "current_page": 1,
        "last_page": 3
      },
      "items": [
        {
          "author": {
            "first_name": "Jacques",
            "last_name_initial": "P.",
            "uuid": "a046adbe-9c7b-56a9-a676-6151a6785dda"
          },
          "coalition": {
            "name": "Education",
            "uuid": "fff11d8d-5cb5-4075-b594-fea265438d65"
          },
          "name": "Cause pour l'education",
          "description": "Lorem ipsum dolor sit amet, consectetur adipiscing elit.",
          "uuid": "fa6bd29c-48b7-490e-90fb-48ab5fb2ddf8",
          "image_url": "http://test.enmarche.code/assets/images/causes/532c52e162feb2f6cfae99d5ed52d41f.png"
        },
        {
          "author": {
            "first_name": "Michelle",
            "last_name_initial": "D.",
            "uuid": "313bd28f-efc8-57c9-8ab7-2106c8be9697"
          },
          "coalition": {
            "name": "Culture",
            "uuid": "d5289058-2a35-4cf0-8f2f-a683d97d8315"
          },
          "name": "Cause pour la culture",
          "description": "Lorem ipsum dolor sit amet, consectetur adipiscing elit.",
          "uuid": "55056e7c-2b5f-4ef6-880e-cde0511f79b2",
          "image_url": "http://test.enmarche.code/assets/images/causes/644d1c64512ab5489ab8590a3b313517.png"
        }
      ]
    }
    """

  Scenario: As a non logged-in user I can paginate causes with default number of causes by page
    Given I add "Accept" header equal to "application/json"
    When I send a "GET" request to "/api/causes?page=2"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "metadata": {
        "total_items": 5,
        "items_per_page": 2,
        "count": 2,
        "current_page": 2,
        "last_page": 3
      },
      "items": [
          {
              "name": "Cause pour la culture 2",
              "description": "Description de la cause pour la culture 2",
              "coalition": {
                  "name": "Culture",
                  "uuid": "d5289058-2a35-4cf0-8f2f-a683d97d8315"
              },
              "uuid": "017491f9-1953-482e-b491-20418235af1f",
              "author": {
                  "uuid": "313bd28f-efc8-57c9-8ab7-2106c8be9697",
                  "first_name": "Michelle",
                  "last_name_initial": "D."
              },
              "image_url": "http://test.enmarche.code/assets/images/causes/73a6283e0b639cbeb50b9b28d401eaca.png"
          },
          {
              "name": "Cause pour la culture 3",
              "description": "Description de la cause pour la culture 3",
              "coalition": {
                  "name": "Culture",
                  "uuid": "d5289058-2a35-4cf0-8f2f-a683d97d8315"
              },
              "uuid": "5f8a6d40-9e69-4311-a45b-67c00d30ad41",
              "author": {
                  "uuid": "313bd28f-efc8-57c9-8ab7-2106c8be9697",
                  "first_name": "Michelle",
                  "last_name_initial": "D."
              },
              "image_url": null
          }
      ]
    }
    """

  Scenario: As a non logged-in user I can paginate causes with specific number of causes by page
    Given I add "Accept" header equal to "application/json"
    When I send a "GET" request to "/api/causes?page_size=5"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "metadata": {
        "total_items": 5,
        "items_per_page": 5,
        "count": 5,
        "current_page": 1,
        "last_page": 1
      },
      "items": [
          {
              "name": "Cause pour l'education",
              "description": "Lorem ipsum dolor sit amet, consectetur adipiscing elit.",
              "coalition": {
                  "name": "Education",
                  "uuid": "fff11d8d-5cb5-4075-b594-fea265438d65"
              },
              "uuid": "fa6bd29c-48b7-490e-90fb-48ab5fb2ddf8",
              "author": {
                  "uuid": "a046adbe-9c7b-56a9-a676-6151a6785dda",
                  "first_name": "Jacques",
                  "last_name_initial": "P."
              },
              "image_url": "http://test.enmarche.code/assets/images/causes/532c52e162feb2f6cfae99d5ed52d41f.png"
          },
          {
              "name": "Cause pour la culture",
              "description": "Lorem ipsum dolor sit amet, consectetur adipiscing elit.",
              "coalition": {
                  "name": "Culture",
                  "uuid": "d5289058-2a35-4cf0-8f2f-a683d97d8315"
              },
              "uuid": "55056e7c-2b5f-4ef6-880e-cde0511f79b2",
              "author": {
                  "uuid": "313bd28f-efc8-57c9-8ab7-2106c8be9697",
                  "first_name": "Michelle",
                  "last_name_initial": "D."
              },
              "image_url": "http://test.enmarche.code/assets/images/causes/644d1c64512ab5489ab8590a3b313517.png"
          },
          {
              "name": "Cause pour la culture 2",
              "description": "Description de la cause pour la culture 2",
              "coalition": {
                  "name": "Culture",
                  "uuid": "d5289058-2a35-4cf0-8f2f-a683d97d8315"
              },
              "uuid": "017491f9-1953-482e-b491-20418235af1f",
              "author": {
                  "uuid": "313bd28f-efc8-57c9-8ab7-2106c8be9697",
                  "first_name": "Michelle",
                  "last_name_initial": "D."
              },
              "image_url": "http://test.enmarche.code/assets/images/causes/73a6283e0b639cbeb50b9b28d401eaca.png"
          },
          {
              "name": "Cause pour la culture 3",
              "description": "Description de la cause pour la culture 3",
              "coalition": {
                  "name": "Culture",
                  "uuid": "d5289058-2a35-4cf0-8f2f-a683d97d8315"
              },
              "uuid": "5f8a6d40-9e69-4311-a45b-67c00d30ad41",
              "author": {
                  "uuid": "313bd28f-efc8-57c9-8ab7-2106c8be9697",
                  "first_name": "Michelle",
                  "last_name_initial": "D."
              },
              "image_url": null
          },
          {
              "name": "Cause pour la justice",
              "description": "Lorem ipsum dolor sit amet, consectetur adipiscing elit.",
              "coalition": {
                  "name": "Justice",
                  "uuid": "5b8db218-4da6-4f7f-a53e-29a7a349d45c"
              },
              "uuid": "44249b1d-ea10-41e0-b288-5eb74fa886ba",
              "author": {
                  "uuid": "a046adbe-9c7b-56a9-a676-6151a6785dda",
                  "first_name": "Jacques",
                  "last_name_initial": "P."
              },
              "image_url": null
          }
      ]
    }
    """

  Scenario: As a non logged-in user I can get one cause by uuid
    Given I add "Accept" header equal to "application/json"
    When I send a "GET" request to "/api/causes/fa6bd29c-48b7-490e-90fb-48ab5fb2ddf8"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
      {
        "author": {
          "first_name": "Jacques",
          "last_name_initial": "P.",
          "uuid": "a046adbe-9c7b-56a9-a676-6151a6785dda"
        },
        "coalition": {
          "name": "Education",
          "uuid": "fff11d8d-5cb5-4075-b594-fea265438d65"
        },
        "name": "Cause pour l'education",
        "description": "Lorem ipsum dolor sit amet, consectetur adipiscing elit.",
        "uuid": "fa6bd29c-48b7-490e-90fb-48ab5fb2ddf8",
        "image_url": "http://test.enmarche.code/assets/images/causes/532c52e162feb2f6cfae99d5ed52d41f.png"
      }
    """

  Scenario: As a non logged-in user I get a 404 when providing an unknown cause uuid
    Given I add "Accept" header equal to "application/json"
    When I send a "GET" request to "/api/causes/70126dd9-4a9e-4dbe-8093-14be4a24b9ed"
    Then the response status code should be 404

  Scenario: As a non logged-in user I can get causes of some coalition
    Given I add "Accept" header equal to "application/json"
    When I send a "GET" request to "/api/coalitions/d5289058-2a35-4cf0-8f2f-a683d97d8315/causes"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
        "metadata": {
            "total_items": 3,
            "items_per_page": 2,
            "count": 2,
            "current_page": 1,
            "last_page": 2
        },
        "items": [
            {
                "name": "Cause pour la culture",
                "description": "Lorem ipsum dolor sit amet, consectetur adipiscing elit.",
                "coalition": {
                    "name": "Culture",
                    "uuid": "d5289058-2a35-4cf0-8f2f-a683d97d8315"
                },
                "uuid": "55056e7c-2b5f-4ef6-880e-cde0511f79b2",
                "author": {
                    "uuid": "313bd28f-efc8-57c9-8ab7-2106c8be9697",
                    "first_name": "Michelle",
                    "last_name_initial": "D."
                },
                "image_url": "http://test.enmarche.code/assets/images/causes/644d1c64512ab5489ab8590a3b313517.png"
            },
            {
                "name": "Cause pour la culture 2",
                "description": "Description de la cause pour la culture 2",
                "coalition": {
                    "name": "Culture",
                    "uuid": "d5289058-2a35-4cf0-8f2f-a683d97d8315"
                },
                "uuid": "017491f9-1953-482e-b491-20418235af1f",
                "author": {
                    "uuid": "313bd28f-efc8-57c9-8ab7-2106c8be9697",
                    "first_name": "Michelle",
                    "last_name_initial": "D."
                },
                "image_url": "http://test.enmarche.code/assets/images/causes/73a6283e0b639cbeb50b9b28d401eaca.png"
            }
        ]
    }
    """

  Scenario: As a non logged-in user I can paginate causes of some coalition
    Given I add "Accept" header equal to "application/json"
    When I send a "GET" request to "/api/coalitions/d5289058-2a35-4cf0-8f2f-a683d97d8315/causes?page=2"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
        "metadata": {
            "total_items": 3,
            "items_per_page": 2,
            "count": 1,
            "current_page": 2,
            "last_page": 2
        },
        "items": [
            {
                "name": "Cause pour la culture 3",
                "description": "Description de la cause pour la culture 3",
                "coalition": {
                    "name": "Culture",
                    "uuid": "d5289058-2a35-4cf0-8f2f-a683d97d8315"
                },
                "uuid": "5f8a6d40-9e69-4311-a45b-67c00d30ad41",
                "author": {
                    "uuid": "313bd28f-efc8-57c9-8ab7-2106c8be9697",
                    "first_name": "Michelle",
                    "last_name_initial": "D."
                },
                "image_url": null
            }
        ]
    }
    """