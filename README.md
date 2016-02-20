IvoazContentEditableBundle
==========================

The `IvoazContentEditableBundle` provides a twig extension for editable content
with internationalization support in Symfony framework.

Example usage:

```twig
<h1>{{ 'Go ahead, edit away!' | contenteditable }}</h1>
```

This saves the content of current locale in the database and lets a user with
`ROLE_ADMIN` permission edit the content with the browser's
[content editable](https://developer.mozilla.org/en-US/docs/Web/Guide/HTML/Content_Editable)
feature.

Here's another example with larger content:
```twig
{% contenteditable "content_editable_description" %}
    <h2>HTMLElement.contentEditable</h2>
    <p>The <strong><code>HTMLElement.contentEditable</code></strong> property is used to indicate whether or not the element is editable.</p>
{% contenteditable %}
```

[![Build Status](https://secure.travis-ci.org/ivoaz/IvoazContentEditableBundle.png)](http://travis-ci.org/liip/IvoazContentEditableBundle)
[![Total Downloads](https://poser.pugx.org/ivoaz/content-editable-bundle/downloads.png)](https://packagist.org/packages/ivoaz/content-editable-bundle)
[![Latest Stable Version](https://poser.pugx.org/ivoaz/content-editable-bundle/v/stable.png)](https://packagist.org/packages/ivoaz/content-editable-bundle)

Requirements
------------

* Symfony ~3.0
* For other library requirements see the `require` section of
  [composer.json](composer.json)

Installation
------------

### Step 1: Download the Bundle


Open a command console, enter your project directory and execute the
following command to download the latest version of this bundle:

```bash
$ composer require ivoaz/content-editable-bundle
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `app/AppKernel.php` file of your project:

```php
<?php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // ...

            new Ivoaz\Bundle\ContentEditableBundle\IvoazContentEditableBundle(),
        );

        // ...
    }

    // ...
}
```

### Step 3: Configure the routing

Now configure the routing with the prefix you want:

```yml
# app/config/routing.yml
ivoaz_contente_ditable:
    resource: "@IvoazContentEditableBundle/Resources/config/routing.xml"
    prefix: admin/contenteditable

```

### Step 4: Install the assets

Lastly, if you are not making your own editor, install the assets needed for the
default editor:

```bash
$ bin/console assets:install --symlink
```

Documentation
=============

For more detailed information about the features of this bundle, please refer to
the [documentation](Resources/doc/index.rst).

License
=======

This bundle is released under the [MIT license](LICENSE)
