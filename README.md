# Dravencms Form module

This is a simple Form module for dravencms

## Instalation

The best way to install dravencms/form is using  [Composer](http://getcomposer.org/):


```sh
$ composer require dravencms/form:@dev
```

Then you have to register extension in `config.neon`.

```yaml
extensions:
	form: Dravencms\Form\DI\FormExtension
```
