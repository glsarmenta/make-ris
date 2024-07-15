# Laravel Artisan Commands for Repository, Interface, and Service Generation

This package provides Artisan commands for Laravel applications to generate Repository, Interface, and Service classes for models, making it easier to adhere to the Repository pattern and keep your code organized.

## Features

### Repository Command
Command that creates a repository and its corresponding interface based on a model name.

### Service Command
Command that creates a service class, optionally linked to a specific controller.

## Installation
Install the package via Composer:
```bash
composer require ynsinc/make-ris:dev-main
```
Register the commands in app/Console/Kernel.php:

```php
protected $commands = [
    \App\Console\Commands\MakeRepositoryCommand::class,
    \App\Console\Commands\MakeServiceCommand::class,
];
```
## Usage
### Repository Command
This command creates a repository and interface based on a model name.

### Basic Command
```bash
php artisan make:repository --model=ModelName
```
Optional Flags
| Flag/Option | Usage | Description |
|-------------|-------|-------------|
| `--model` | `--model=User` | Add a repository and interface based on the model name. |
| `--subdir` | `--subdir=User` | Add a service under a specific directory. |
| `--name` | `--name=CustomName` | Specify a custom name (if needed). |


### Examples
**With Model Flag**

```bash
php artisan make:repository --model=User
```

Outcome:
```bash
app/
└── Interfaces/
    └── UserInterface.php
└── Repositories/
    └── UserRepository.php
```
**With Subdirectory Flag**

```
php artisan make:repository --model=User --subdir=User
```
Outcome:

```
app/
└── Interfaces/
    └── User/
        └── UserInterface.php
└── Repositories/
    └── User/
        └── UserRepository.php
```
**With Name Flag**

```bash
php artisan make:repository --name=Custom
```

Outcome:
```bash
app/
└── Interfaces/
    └── CustomInterface.php
└── Repositories/
    └── CustomRepository.php
```

Service Command
This command creates a service class.

Basic Command
```bash
php artisan make:service
```
| Flag/Option  | Usage                          | Description                                                                                           |
|--------------|--------------------------------|-------------------------------------------------------------------------------------------------------|
| --controller | --controller=UserManagement    | Add a service based on the controller name.                                                           |
| --subdir     | --subdir=User                  | Add a service under a specific directory.                                                             |
| --name       | --name=CustomName              | Add a custom service. (This is a service that is not affiliated to any controller such as Common Services like StripeService, TaxService etc...) |

## Examples
With Controller Flag

```bash
php artisan make:service --controller=UserManagement
```
Outcome:

```markdown
app/
└── Services/
    └── UserManagementService.php
```
With Subdirectory Flag

```bash
php artisan make:service --controller=UserManagement --subdir=User
```

Outcome:

```markdown
app/
└── Services/
    └── User/
        └── UserManagementService.php
```
With Name Flag

```bash
php artisan make:service --name=Tax --subdir=Common
```
Outcome:

```markdown
app/
└── Services/
    └── Common/
        └── TaxService.php
```
Output
Repository Command Output Structure:

```markdown
app/
└── Interfaces/
    └── [Subdirectory/]InterfaceName.php
└── Repositories/
    └── [Subdirectory/]RepositoryName.php
```
Service Command Output Structure:

```markdown
app/
└── Services/
    └── [Subdirectory/]ServiceName.php
```

Contributing
Feel free to submit issues or pull requests if you find any bugs or have suggestions for improvements.

## License
This project is open-sourced software licensed under the MIT license.