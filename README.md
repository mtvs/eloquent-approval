![Build Status](https://github.com/mtvs/eloquent-approval/actions/workflows/build.yml/badge.svg)

![eloquent-approval-preview](https://user-images.githubusercontent.com/8286154/172069783-52fd5b91-e032-4c1a-9094-9611abe4e3c8.png)

# Eloquent Approval

Approval process for Laravel's Eloquent models.

## Why we need content approval in our apps

Unless you're comfortable with unacceptable content, spam and any other 
violations that may appear in what the users post, you need to include some
sort of content approval in your app.

## Why approval process with three states

Although it's possible to approve a model by using a boolean field but a field 
that has three possible values: pending, approved and rejected gives us more 
power. It differentiates between the models waiting for the decision and the 
rejected ones and also makes it clear for the user if their content gets rejected.

## How it works

After the setup, when new entities are being created, they are marked as 
_pending_. Then their status can be changed to _approved_ or _rejected_.

Also, when an update occurs that modifies attributes that require approval the
entity becomes _suspended_ again.

By default the approval scope is applied on every query and filters out the
_pending_ and _rejected_ entities, so only _approved_ entities are included.
You can include the entities that aren't _approved_ by explicitly specifying it.

## Install

```sh
$ composer require mtvs/eloquent-approval
```

## Setup

### Registering the service provider

By default the service provider is registered automatically by Laravel package
discovery otherwise you need to register it in your `config\app.php`

```php
Mtvs\EloquentApproval\ApprovalServiceProvider::class
```
### Database

The following method adds two columns to the schema, one to store
the _approval status_ named `approval_status` and another to store the _timestamp_ at which the 
last status update is occurred named `approval_at`.

```php
$table->approvals()
```

You can change the default column names but then you need to specify them on the model too.

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

If you want to change the default column names you need to specify them
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

> Add `approval_at` to the model `$dates` list to get `Carbon` instances when accessing it.

#### Approval Required Attributes

When an update occurs that modifies attributes that require
approval, the entity becomes _suspended_ again.

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

They work like `$fillable` and `$guarded` in the Eloquent. `approvalRequired()` returns
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

If you want to disable the approval scope totally on every query, you can set
the `approvalScopeDisabled` on the model.

```php
use Illuminate\Database\Eloquent\Model;
use Mtvs\EloquentApproval\Approvable;

class Entity extends Model
{
    use Approvable;
    
    public $approvalScopeDisabled = true;
}
```

### Limiting to only a specific status

```php
Entity::onlyPending()->get(); // retrieving only pending entities
Entity::onlyRejected()->get(); // retrieving only rejected entities
Entity::onlyApproved()->get(); // retrieving only approved entities
```

### Updating the status 

#### On model objects

You can update the status of an entity by using provided methods on the `Model`
object.

```php
$entity->approve(); // returns bool if the entity exists otherwise null  
$entity->reject(); // returns bool if the entity exists otherwise null  
$entity->suspend(); // returns bool if the entity exists otherwise null  
```

#### On `Builder` objects

You can update the status of more than one entity by using provided methods on `Builder`
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

There are some model events that are dispatched before and after each approval action.

| Action  | Before     | After     |
|---------|------------|-----------|
| approve | approving  | approved  |
| suspend | suspending | suspended |
| reject  | rejecting  | rejected  |

Also, there is a general event named `approvalChanged` that is dispatched whenever
the approval status is changed regardless of the actual status.

You can hook to them by calling the provided `static` methods, which are named 
after them, and passing your callbacks. Or by registring observers with methods
with the same names.

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
            // You can halt the process by returning false
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
        // You can halt the process by returning false
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
no event will be dispatched and the approval timestamp won't be updated. 
In this case the approval method returns `false`.

## The Model Factory

Import the `ApprovalFactoryStates` to be able to use the approval states
when using the model factory.

```php
    namespace Database\Factories;

    use Illuminate\Database\Eloquent\Factories\Factory;
    use Mtvs\EloquentApproval\ApprovalFactoryStates;

    class EntityFactory extends Factory
    {
        use ApprovalFactoryStates;

        public function definition()
        {
            //
        }
    }
```
```php
    Entity::factory()->approved()->create();
    Entity::factory()->rejected()->create();
    Entity::factory()->suspended()->create();
```
## Handling Approval HTTP Requests

You can import the `HandlesApproval` in a controller to perform the approval
operations on a model. It contains an abstract method which has to be implemented
to return the model's class name.

```php
    namespace App\Http\Controllers\Admin;

    use App\Http\Controllers\Controller;
    use App\Models\Entity;
    use Mtvs\EloquentApproval\HandlesApproval;

    class EntitiesController extends Controller
    {
        use HandlesApproval;

        protected function model()
        {
            return Entity::class;
        }
    }

```

The trait's `performApproval()` does the approval and the request should be
routed to this method. It has the `key` and `request` parameters which are 
passed to it by the router.

When do the routing, don't forget to apply the `auth` and `can` middlewares for
authentication and authourization.

```php
    Route::post(
        'admin/enitiy/{key}/approval', 
        'Admin\EntitiesController@performApproval'
    )->middleware(['auth', 'can:perform-approval'])
```

The request must have a `approval_status` key with
one of the possible values: `approved`, `pending`, `rejected`.

## Frontend Components

There are also some UI components here written for Vue.js and Bootstrap that 
you can use. First install them using the `approval:ui` artisan command and 
then register them in your app.js file.

### Approval Buttons Component

Call `<approval-buttons>` and pass the `current-status` and the `approval-url` 
props to be able to make HTTP requests to set the approval status.

It emits the `approval-changed` event when an approval action happens. 
The payload of the event is an object with the new `approval_status` and 
`approval_at` values. Use the event to modify the corresponding keys on the
`entity` that in turn should change the `current-status` prop on the following
cycle.

### Approval Status Component

Call `<approval-status>` and pass the `value` prop to show the current status.

## Support

If you liked this project and want to support the author, you can contact
[mtvsdev@gmail.com](mailto:mtvsdev@gmail.com) Thanks.

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