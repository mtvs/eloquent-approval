# Eloquent Approval

## What is it?

Eloquent Approval provides approval process for Laravel's Eloquent models.

## How it works?

It does it by using a dedicated column to strore the _approval status_ of an entity.
New entities are marked as _pending_ and then can become _approved_
or _rejected_.

When querying the model only _approved_ entities will be retrieved by default.
In this way the _rejected_ entities as well as _pending_ ones will be excluded.
You can include those by explicitly specifying it. 