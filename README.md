# Dravencms Form module

This is a simple Form module for dravencms

## Instalation

The best way to install dravencms/form is using  [Composer](http://getcomposer.org/):


```sh
$ composer require dravencms/form
```

Then you have to register extension in `config.neon`.

```yaml
extensions:
    form: Dravencms\Form\DI\FormExtension
```

## Admin authorization and mutations

Administration uses User 3.1, Admin 3.2 and Application 0.3 guards. Screens and write callbacks enforce the existing provider-defined permissions server-side, including direct URLs and bulk callbacks. Mutating signals require POST and a session-bound action token; synchronize the matching admin assets when upgrading. See the User package's `user:security:migrate` instructions for the password-reset schema change.

## Deleting forms and saved data

Deleting a form removes its groups, fields, choices, translations and saved values through Doctrine's removal cascades. Deleting a group or field removes only its own descendants and saved values; deleting a choice preserves the field's saved answers. Shared locales, other forms and other fields' answers are preserved. Deleting a saved submission removes its values while keeping the form definition.

Single and bulk admin deletions flush once, so a database constraint failure rolls back the whole operation. Submission headers are independent records and are not removed when deleting a field or form.

Version 2.7.2 changes ORM metadata only and requires no database migration. Clear the application's Doctrine metadata and compiled container caches when upgrading.
