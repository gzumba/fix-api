# Fixably interview assignment

## Installation

* Clone the repo
* composer install

Edit .env to have a code for token retrieval.
API_CODE=<somecode>

## Tests

The ApiOrderService public methods are tested against the real API endpoint

* bin/phpunit
 
## Usage

The tasks were implemented as Symfony Console commands. Some parameters are
supported, but all commands have defaults picked from the assignment.

### List available commands:

````bash
bin/console list fixably
````

````
Available commands for the "fixably" namespace:
  fixably:order-status-counts  Show counts of orders with each status
  fixably:orders               Fetch all orders for a Brand with an assigned Technician
  fixably:submit-order         Submit a new Repair Order
  fixably:weekly-sales         Show sales report for a period
````

### Help for a command
````bash
bin/console fixably:orders --help
````
````
Description:
  Fetch all orders for a Brand with an assigned Technician

Usage:
  fixably:orders [<brand>]

Arguments:
  brand                 Brand to fetch [default: "iPhone"]
````

## Tasks

### Task 1:

The token fetching is handled in the background, so no separate command for that

### Task 2:

The description was slightly vague regarding what statistics to fetch... So
I made a simple interpretation:

````bash
bin/console fixably:order-status-counts
````
````
+----------+-------+
| Status   | Count |
+----------+-------+
| Assigned | 1415  |
| Closed   | 855   |
| Open     | 537   |
| Unpaid   | 223   |
+----------+-------+
````

### Task 3:

````bash
bin/console fixably:orders iPhone
````
````
+-------+------------+----------+------------+-------------------+
| Id    | Created    | Status   | Technician | Device            |
+-------+------------+----------+------------+-------------------+
| 9289  | 01.10.2020 | Assigned | Mira       | iPhone X          |
| 9363  | 06.10.2020 | Assigned | Seppo      | iPhone 8          |
| 9368  | 06.10.2020 | Assigned | Katri      | iPhone X          |
| 9395  | 06.10.2020 | Assigned | Iida       | iPhone 12 Pro Max |
| 9399  | 08.10.2020 | Assigned | Seppo      | iPhone 12 Pro Max |
| 9459  | 13.10.2020 | Assigned | Pekka      | iPhone 12 Pro Max |
| 9485  | 13.10.2020 | Assigned | Suvi       | iPhone 8          |
| 9496  | 13.10.2020 | Assigned | Pasi       | iPhone 8          |
| 9517  | 14.10.2020 | Assigned | Katri      | iPhone 12 Pro Max |
.
.
.

````

### Task 4:

````bash
bin/console fixably:weekly-sales 2020-11-1 2020-12-1
````
````
+----------+----------+------------------+--------------+----------+
| Week     | Invoices | Invoice Change % | Total amount | Change-% |
+----------+----------+------------------+--------------+----------+
| 2020, 45 | 77       |                  | 18454.23     |          |
| 2020, 46 | 82       | 6.5              | 21604.18     | 17.1     |
| 2020, 47 | 75       | -8.5             | 15344.25     | -29      |
| 2020, 48 | 73       | -2.7             | 15544.27     | 1.3      |
| 2020, 49 | 32       | -56.2            | 7679.68      | -50.6    |
+----------+----------+------------------+--------------+----------+
````

### Task 5:

````bash
bin/console fixably:submit-order
````

The application will ask questions interactively:

````

Device Type [Laptop]:
[0] Laptop
[1] Phone
[2] Tablet
>

Manufacturer [Apple]:
>

Brand [MacBook Pro]:
>

Issue description [Broken screen]:
>


[OK] Order created with Id: 14574


+--------------+------------------+
| Id           | 14574            |
| Type         | Laptop           |
| Manufacturer | Apple            |
| Brand        | MacBook Pro      |
| Created      | 07.05.2022 09:16 |
+--------------+------------------+
+------------------+-------+---------------+
| 07.05.2022 09:16 | Issue | Broken screen |
+------------------+-------+---------------+
````

## Notes

The implementation process went basically so that I created a command
and added the necessary methods to the OrderService interface.

Then I wrote a test for the method against the ApiOrderService to
verify I get back data in expected format.

As I had access to an API endpoint for testing data I decided not
to mock any HTTP calls, though the HttpClient has quite good support for that
and I typically have to do it as there is no test endpoints for use.

The token fetching is automated before each API call.

The error handling is very basic, but should handle typical API failures.

[src/Command](src/Command) contains the Symfony Console Commands
that handle the input/output. Instead of Commands one could very
easily implement some Controllers for web page rendering or
whatever. 

The Commands use only an interface for fetching Domain formatted DTOs

[src/Service](src/Service) contains the OrderService interface and one
implementation. The interface is very top-level and does not expose
any knowledge of the real API.

[src/Dto](src/Dto) contains the Domain models for communicating with the
OrderService interface.

