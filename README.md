[![Build Status](https://travis-ci.org/mtvs/eloquent-approval.svg?branch=master)](https://travis-ci.org/mtvs/eloquent-approval)

# Eloquent Approval

Approval process for Laravel's Eloquent models.

## How it works?

New entities are marked as _pending_ and then can become _approved_ or _rejected_.

When querying the model only _approved_ entities are included meaning _rejected_
entities as well as _pending_ ones are excluded. You can include those by
explicitly specifying it.

When an update occurs that modifies attributes that require approval the entity
becomes _suspended_ again.

## Install

```sh
$ composer require mtvs/eloquent-approval
```

### Version Compatibility

`"laravel/framework": "5.5.*|5.6.*|5.7.*"`

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

> Add `approval_at` to the model `$dates` to get `Carbon` instances when accessing it.

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
/**
 * @return array
 */
public function approvalRequired()
{
    return ['*'];
}

/**
 * @return array
 */
public function approvalNotRequired()
{
    return [];
}
```

You can override them to have a custom set of approval required attributes.

They work like `$fillable` and `$guarded` in Eloquent. `approvalRequired()` returns
the _black list_ while `approvalNotRequired()` returns the _white list_.  

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

Entity::anyApprovalStatus()->find(1); // retrieving one

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

You can update the status of an entity by using provided methods on the `Model`
object.

```php
$entity->approve(); // returns bool if the entity exists otherwise null  
$entity->reject(); // returns bool if the entity exists otherwise null  
$entity->suspend(); // returns bool if the entity exists otherwise null  
```

#### On `Builder` objects

You can update the statuses of entities by using provided methods on `Builder`
objects.

```php
Entity::whereIn('id', $updateIds)->approve(); // returns number of updated
Entity::whereIn('id', $updateIds)->reject(); // returns number of updated
Entity::whereIn('id', $updateIds)->suspend(); // returns number of updated
```

#### Approval Timestamp

When you change the approval status of an entity its `approval_at` column updates.
Before the first approval action on an entity its`approval_at` is `null`. 

### Check the status of an entity

You can check the status of an entity using provided methods on `Model` objects.

```php
$entity->isApproved(); // returns bool if entity exists otherwise null
$entity->isRejected(); // returns bool if entity exists otherwise null
$entity->isPending(); // returns bool if entity exists otherwise null
```

### Approval Events

There are some model events that dispatched before and after each approval action.

| Action  | Before     | After     |
|---------|------------|-----------|
| approve | approving  | approved  |
| suspend | suspending | suspended |
| reject  | rejecting  | rejected  |

Also, there is a general event named `approvalChanged` that is dispatched whenever
the approval status is changed regardless of the actual status.

You can hook to them by calling the provided `static` methods named after them
and passing your callbacks or by registring observers with methods with the same names.

```php
use Illuminate\Database\Eloquent\Model;
use Mtvs\EloquentApproval\Approvable;

class Entity extends Model
{
    use Approvable;
    
    protected static function boot()
    {
        parent::boot();
        
        static::approving(function ($entity) {
            // You can prevent it by returning false
        });
        
        static::approved(function ($entity) {
            // $entity has been approved
        });

        // or:

        static::observe(ApprovalObserver::class);
    }
}

class ApprovalObserver
{
    public function approving($entity)
    {
        // You can prevent it by returning false
    }

    public function approved($entity)
    {
        // $entity has been approved
    }
}
```

[Eloquent model events](https://laravel.com/docs/eloquent#events) can also be mapped to your application event classes.

## Duplicate Approvals

Trying to set the approval status to the current value is ignored, i.e.: 
no event is dispatched and the approval timestamp doesn't update. In this case
the approval method returns `false`.

## Development / Contribution

### Run tests

```sh   
$ composer test
```

## Inspirations

When I was searching for an existing package for approval functionality
on eloquent models I encountered [hootlex/laravel-moderation](https://github.com/hootlex/laravel-moderation)
even though I decided to write my own package I got some helpful inspirations from that one.

I also wrote different parts of the code following the way that similar parts 
of [Eloquent](https://github.com/laravel/framework/tree/master/src/Illuminate/Database/Eloquent) itself is written.