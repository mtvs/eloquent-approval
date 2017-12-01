# Eloquent Approval

## What is it?

Eloquent Approval provides approval process for Laravel's Eloquent models.

## How it works?

It does it by using a dedicated column to store the _approval status_ of an entity.

New entities are marked as _pending_ and then can become _approved_
or _rejected_. Also when an update occurs that modifies attributes that require
approval the entity becomes _suspended_ again.

When querying the model only _approved_ entities will be retrieved by default.
In this way the _rejected_ entities as well as _pending_ ones will be excluded.
You can include those by explicitly specifying it.

## Install



## Setup

### Registering the service provider

By default the service provider is registered automatically by Laravel package
discovery otherwise you need to register it in your `config\app.php`

```php
Mtvs\EloquentApproval\ApprovalServiceProvider::class
```


### Database

You need to add two columns to your model's database schema, one to store
the _approval status_ itself and another to store the timestamp at which the 
last status update has occurred.

```php
$table->tinyInteger('approval_status');
$table->timestamp('approval_at')->nullable();
```

You can change the default column names but then you need to specify them on the model.

### Model 

Add `Approvable` trait to the model

```php    
use Illuminate\Database\Eloquent\Model;
use Mtvs\EloquentApproval\Approvable;

class Entity extends Model
{
    use Approvable;
}
```

If you decided to change the default column names you need to specify them
by adding class constants to your model

```php    
use Illuminate\Database\Eloquent\Model;
use Mtvs\EloquentApproval\Approvable;

class Entity extends Model
{
    use Approvable;
    
    const APPROVAL_STATUS = 'custom_approval_status';
    const APPROVAL_AT = 'custom_approval_at';
}
```

#### Approval Required Attributes

When an update occurs that modifies attributes that require
approval the entity becomes _suspended_ again.

```php
$entity->update($attributes); // an update with approval required modification

$entity->isPending(); // true
```

> Note that this happens only when you perform the _update_ on `Model` object
itself not by using a query `Builder` instance.

By default all attributes require approval.

```php
protected $approval_required = ['*'];

protected $approval_not_required = [];
```

You can override them to have a custom set of approval required attributes.

They work like `$fillable` and `$guarded` in Eloquent. `$approval_required` is
the _black list_ while $approval_not_required` is the _white list_.  

## Usage

Newly created entities are marked as _pending_ and by default excluded from 
queries on the model. 

```php
Entity::create(); // #1 pending

Entity::all(); // []

Entity::find(1); // null
```

### Including all the entities

```php
Entity::anyApprovalStatus()->get(); // retrieving all

Entity::anyApprovalStatus()->find(1); // retrieving a non approved entity

Entity::anyApprovalStatus()->delete(); // deleting all
```

### Limiting to only a specific status

```php
Entity::onlyPending()->get(); // retrieving only pending entities
Entity::onlyRejected()->get(); // retrieving only rejected entities
Entity::onlyApproved()->get(); // retrieving only approved entities
```

### Updating status 

#### On model objects

You can update the status of an entity by using provided methods on the model
object.

```php
$entity->approve(); // returns bool if the entity exists otherwise null  
$entity->reject(); // returns bool if the entity exists otherwise null  
$entity->suspend(); // returns bool if the entity exists otherwise null  
```

#### On `Builder` objects

You can update the statuses of entities using provided methods on `Builder`
objects.

```php
Entity::whereIn('id', $updateIds)->approve(); // returns number of updated
Entity::whereIn('id', $updateIds)->reject(); // returns number of updated
Entity::whereIn('id', $updateIds)->suspend(); // returns number of updated
```

#### Timestamps refresh

When you update the status of an entity its `approval_at` and `updated_at`
columns are both refreshed. Before the first approval action on an entity its
`approval_at` is `null`. 

### Check the status of an entity

You can check the status of an entity using provided methods on model objects.

```php
$entity->isApproved(); // returns bool if entity exists otherwise null
$entity->isRejected(); // returns bool if entity exists otherwise null
$entity->isPending(); // returns bool if entity exists otherwise null
```

## Development / Contribution

### Tests

To run tests simply execute `phpunit`

```shell    
phpunit
```
    
or if the alias is not defined on your system

```shell
vendor/bin/phpunit
```